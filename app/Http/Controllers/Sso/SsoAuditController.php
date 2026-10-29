<?php

namespace App\Http\Controllers\Sso;

use App\Http\Controllers\Api\Controller;
use App\Http\Transformers\SsoLogTransformer;
use App\Models\Sso\OpenKabSsoLog;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SsoAuditController extends Controller
{
    /**
     * Dashboard audit SSO (super admin). Menjawab view dan ajax DataTables.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            return $this->fractal($this->list(), new SsoLogTransformer, 'sso-logs')->respond();
        }

        $admins = ['' => 'Semua'] + User::query()->orderBy('username')->pluck('username', 'id')->toArray();
        $statuses = [
            '' => 'Semua',
            'success' => 'Berhasil',
            'failed' => 'Gagal',
        ];

        return view('sso.audit', compact('admins', 'statuses'));
    }

    protected function list()
    {
        return QueryBuilder::for(OpenKabSsoLog::query()->with(['admin']))
            ->allowedFields('*')
            ->allowedFilters([
                AllowedFilter::callback('created_at', function ($query, $value) {
                    return $query->whereBetween('attempt_time', $value);
                }),
                AllowedFilter::exact('status'),
                AllowedFilter::exact('desa_id'),
                AllowedFilter::exact('admin_id'),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where(function ($query) use ($value) {
                        $query->where('desa_id', 'LIKE', '%'.$value.'%')
                            ->orWhere('ip_address', 'LIKE', '%'.$value.'%')
                            ->orWhere('reason_if_failed', 'LIKE', '%'.$value.'%');
                    });
                }),
            ])
            ->allowedSorts(['attempt_time'])
            ->defaultSort('-attempt_time')
            ->jsonPaginate();
    }
}
