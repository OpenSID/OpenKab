<?php

namespace App\Http\Repository\CMS;

use App\Http\Repository\BaseRepository;
use App\Models\CMS\Menu;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class MenuRepository extends BaseRepository
{
    protected $fieldSearchable = [
        'name',
        'url',
        'sequence',
        'position',
        'parent_id',
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
        $menus = $this->tree()->toArray();

        $this->removeEmptyChildren($menus);

        return json_encode($menus);
    }

    public function tree(): Collection|null
    {
        return $this->model->selectRaw("id, parent_id , name as text, url as href, 'fas fa-list' as icon")
            ->whereNull('parent_id')
            ->with(['children' => function ($q) {
                $q->selectRaw("id, parent_id , name as text, url as href, 'fas fa-list' as icon");
            }])
            ->orderBy('sequence')
            ->get();
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
        $this->model->whereNotNull('id')->delete();
        try {
            // hapus data lama lalu buat lagi
            $json = json_decode($input['json_menu'], 1);

            return $this->loopTree($json);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function loopTree(array $elements, $parentId = null)
    {
        $sequence = 1;
        foreach ($elements as $element) {
            $input = [
                'name' => $element['text'],
                'url' => $element['href'],
                'sequence' => $sequence,
                'parent_id' => $parentId,
            ];
            $model = parent::create($input);
            if (isset($element['children'])) {
                $this->loopTree($element['children'], $model->id);
            }
            $sequence++;
        }

        return $model;
    }
}
