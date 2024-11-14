<?php

namespace App\Http\Repository\CMS;

use App\Http\Repository\BaseRepository;
use App\Models\CMS\Menu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class MenuRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'icon',
        'menu_type',
        'name',
        'url',
        'sequence',
        'position',
        'parent_id',
        'is_show',
    ];

    public function getFieldsSearchable(): array
    {
        return $this->fieldSearchable;
    }

    public function model(): string
    {
        return Menu::class;
    }

    public function treeJson(): string
    {
        $type = 1;
        if (request('type') != null) {
            $type = request('type');
        }
        $menus = $this->tree($type)->toArray();

        $this->removeEmptyChildren($menus);

        return json_encode($menus);
    }

    public function tree(): ?Collection
    {
        return $this->model->selectRaw("id, parent_id , name as text, url as href, is_show,'fas fa-list' as icon")
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->selectRaw("id, parent_id , name as text, url as href, is_show,'fas fa-list' as icon");
            }])
            ->orderBy('sequence')
            ->get() ?? collect();
    }


    private function removeEmptyChildren(&$array)
    {
        foreach ($array as &$item) {
            if (empty($item['children'])) {
                unset($item['children']);
            } else {
                $this->removeEmptyChildren($item['children']);
            }
        }
    }

    /**
     * Create model record.
     */
    public function create(array $input): Model
    {
        $menu_type = $input['menu_type'] != null ? $input['menu_type'] : 1;

        $this->model->where('menu_type', $menu_type)->whereNotNull('id')->delete();

        try {
            // hapus data lama lalu buat lagi
            $json = json_decode($input['json_menu'], 1);

            return $this->loopTree($json, $menu_type);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function loopTree(array $elements, $menu_type, $parentId = null)
    {
        $sequence = 1;
        foreach ($elements as $element) {
            $input = [
                'menu_type' => $menu_type,
                'name' => $element['text'],
                'url' => $element['href'],
                'icon' => $element['icon'],
                'sequence' => $sequence,
                'parent_id' => $parentId,
                'is_show' => isset($element['is_show']) ? $element['is_show'] : true,
            ];
            $model = parent::create($input);
            if (isset($element['children'])) {
                $this->loopTree($element['children'], $menu_type, $model->id);
            }
            $sequence++;
        }

        return $model;
    }
}
