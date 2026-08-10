<?php

use App\Models\PasswordHistory;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'password' => 'OldP@ssw0rd123!',
    ]);
});

it('stores old password hash in password_histories when password is changed', function () {
    $this->user->password = 'NewP@ssw0rd456!';
    $this->user->save();

    $this->assertDatabaseHas('password_histories', [
        'user_id' => $this->user->id,
        'reason' => 'password_change',
    ]);

    $history = PasswordHistory::where('user_id', $this->user->id)->first();
    expect(Hash::check('OldP@ssw0rd123!', $history->password))->toBeTrue();
});

it('uses custom reason when passwordHistoryReason is set', function () {
    $this->user->passwordHistoryReason = 'custom_reason';
    $this->user->password = 'NewP@ssw0rd456!';
    $this->user->save();

    $this->assertDatabaseHas('password_histories', [
        'user_id' => $this->user->id,
        'reason' => 'custom_reason',
    ]);
});

it('does not store history for new users', function () {
    $newUser = User::factory()->create([
        'password' => 'FirstP@ssw0rd!',
    ]);

    expect(PasswordHistory::where('user_id', $newUser->id)->count())->toBe(0);
});

it('does not store history when password is not changed', function () {
    $this->user->name = 'Updated Name';
    $this->user->save();

    expect(PasswordHistory::where('user_id', $this->user->id)->count())->toBe(0);
});

it('prunes old history entries beyond the configured limit', function () {
    $historyCount = config('password.history_count', 10);

    for ($i = 0; $i < $historyCount; $i++) {
        $this->user->passwordHistory()->create([
            'password' => Hash::make("OldP@ssw0rd{$i}!"),
            'reason' => 'password_change',
        ]);
    }

    expect(PasswordHistory::where('user_id', $this->user->id)->count())->toBe($historyCount);

    $this->user->password = 'NewP@ssw0rdLatest!';
    $this->user->save();

    $remaining = PasswordHistory::where('user_id', $this->user->id)->count();
    expect($remaining)->toBe($historyCount)
        ->and($remaining)->toBeLessThanOrEqual($historyCount);
});

it('resets oldPasswordHash to null after processing', function () {
    $observer = new UserObserver;

    $observer->saving($this->user);
    expect(isset($this->user->oldPasswordHash))->toBeFalse();

    $this->user->password = 'NewP@ssw0rd456!';

    $observer->saving($this->user);
    expect(isset($this->user->oldPasswordHash))->toBeTrue();

    $this->user->passwordHistoryReason = 'test';
    $observer->saved($this->user);
    expect(isset($this->user->oldPasswordHash))->toBeFalse();
});

it('stores history with correct user context', function () {
    $otherUser = User::factory()->create([
        'password' => 'OtherP@ss!1234',
    ]);

    $this->user->password = 'NewP@ssw0rd456!';
    $this->user->save();

    $otherUser->password = 'OtherNewP@ss!5678';
    $otherUser->save();

    expect(PasswordHistory::where('user_id', $this->user->id)->count())->toBe(1);
    expect(PasswordHistory::where('user_id', $otherUser->id)->count())->toBe(1);
});

it('prunes only current user history entries', function () {
    $otherUser = User::factory()->create([
        'password' => 'OtherP@ss!1234',
    ]);

    for ($i = 0; $i < 10; $i++) {
        $this->user->passwordHistory()->create([
            'password' => Hash::make("OldP@ssw0rd{$i}!"),
            'reason' => 'password_change',
        ]);
        $otherUser->passwordHistory()->create([
            'password' => Hash::make("OtherOldP@ss{$i}!"),
            'reason' => 'password_change',
        ]);
    }

    $this->user->password = 'NewP@ssw0rdLatest!';
    $this->user->save();

    expect(PasswordHistory::where('user_id', $this->user->id)->count())->toBe(10);
    expect(PasswordHistory::where('user_id', $otherUser->id)->count())->toBe(10);
});
