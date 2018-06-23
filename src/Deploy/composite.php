<?php
namespace Jiny\Lamp\Deploy;

/**
 * 폴더구조
 */
class Composite extends Component
{
    public $children;

    public function __construct($name)
    {
        //echo __CLASS__."가 생성이 되었습니다.<br>";
        $this->setName($name);
    }

    /**
     * 노드를 추가합니다.
     */
    public function addNode(component $folder)
    {
        // 배열 원소 가합니다.
        $name = $folder->getName();
        //echo "폴더 ".$name."를 추가합니다.<br>";
        $this->children[$name] = $folder;
    }

    public function removeNode($component)
    {
        // 배열 원소를 제거합니다.
    }

}