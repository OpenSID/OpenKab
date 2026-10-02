<?php

namespace App\Http\Transformers;

use App\Models\Sso\OpenKabSsoLog;
use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class SsoLogTransformer extends TransformerAbstract
{
    public function transform(OpenKabSsoLog $log)
    {
        $date = $log->attempt_time
            ? Carbon::parse($log->attempt_time)->setTimezone(config('app.timezone'))->format('d-m-Y H:i:s')
            : null;

        return [
            'id' => $log->id,
            'admin_name' => $log->admin?->name,
            'admin_username' => $log->admin?->username,
            'desa_id' => $log->desa_id,
            'attempt_time' => $date,
            'status' => $log->status,
            'reason_if_failed' => $log->reason_if_failed,
            'ip_address' => $log->ip_address,
            'user_agent' => $log->user_agent,
            'token_fingerprint' => $log->token_fingerprint,
        ];
    }
}
