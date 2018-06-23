<?php

namespace Jiny\Lamp\Deploy;

/**
 * 파일구조
 */
// 컴포넌트 추상화를 적용
class Leaf extends Component
{
    private $data;

    public function __construct($name)
    {
        //echo __CLASS__."가 생성이 되었습니다.<br>";
        $this->setName($name);
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
    }
}