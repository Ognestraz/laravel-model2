<?php namespace Model\Traits;

trait Pagination
{
    static public function pagination($list, $pagination, $view = null) 
    {
        $view = $view ? $view : 'pagination.default';
        $param = array('limit' => $pagination->count());
        if ($list instanceof Illuminate\Database\Eloquent\Relations\Relation) {
            $param['module'] = strtolower(class_basename($list->getParent()));
            $where = $list->getQuery()->getQuery()->wheres;
        } else {
            $where = $list->getQuery()->wheres;
        }

        foreach($where as $item) {
            if (!empty($item['operator']) && $item['operator'] === '=' && isset($item['value'])) {
                $part = explode('.', $item['column']);
                $column = sizeof($part) > 1 ? $part[1] : $part[0];
                $param[$column] = $item['value'];
            }
        }

        $pagination->setBaseUrl(url('/').'/'.strtolower(class_basename($list->getModel())).'/items');

        return $pagination->appends($param)->links($view);
    }
}
