<?php

namespace App\Http\Transformers;

use App\Models\Setting;
use League\Fractal\TransformerAbstract;

class SettingTransformer extends TransformerAbstract
{
    public function transform(Setting $setting)
    {
        return [
            'id' => $setting->id,
            'key' => $setting->key,
            'name' => $setting->name,
            'value' => $this->mapValue($setting),
            'type' => $setting->type,
            'attribute' => $setting->attribute,
            'description' => $setting->description,
            'created_at' => $setting->created_at,
            'updated_at' => $setting->updated_at,
        ];
    }

    private function mapValue(Setting $setting)
    {
        switch($setting->type) {
            case 'dropdown':
                return collect($setting->attribute)->pluck('text', 'value')[$setting->value] ?? '-';
                break;
        }

        return $setting->value;
    }
}
