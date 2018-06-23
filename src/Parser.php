<?php
namespace Jiny\Lamp;

use \Jiny\Core\Registry;

class Parser
{

    public $_theme;
    public $_path;

    public $_html;
    public $_body;

    public $_html_head;
    public $_html_header;
    public $_html_nav;
    public $_html_footer;

    public $_pos=[];

    public function __construct()
    {
        //echo __CLASS__." 객체를 생성하였습니다.\n";
        $this->_path = "./theme/";
    }

    public function setTheme($name)
    {
        $this->_theme = $name;
    }

    public function process($html)
    {
        $this->_html = $html;

        $this->exHead();

        $this->exHeader();

        // 레이아웃 저장
        file_put_contents($this->_path.$this->_theme.DS."layout.htm", $this->_html );
    }

    public function exHead()
    {
        echo "문서에서 HEAD 영역을 분리합니다.\n";
        $head = $this->getTagBody($this->_html, "<head");
        file_put_contents($this->_path.$this->_theme.DS."head.htm", $head );
        $this->_html = str_replace($head, "{% include 'head.htm' %}", $this->_html);

        echo "문서에서 BODY 영역을 분리합니다.\n";
        $body = $this->getTagBody($this->_html, "<body");
        $this->_body = $body;

        return $this;
    }

    public function exHeader()
    {
        echo "BODY에서 Header 영역을 분리합니다.\n";
        $header = $this->getTagBody($this->_html, "<header");
        file_put_contents($this->_path.$this->_theme.DS."header.htm", $header);        

        echo "BODY에서 Nav 영역을 분리합니다.\n";
        $nav = $this->getTagBody($this->_html, "<nav");
        file_put_contents($this->_path.$this->_theme.DS."nav.htm", $nav);        

        echo "BODY에서 Footer 영역을 분리합니다.\n";
        $footer = $this->getTagBody($this->_html, "<footer");
        file_put_contents($this->_path.$this->_theme.DS."footer.htm", $footer);

        $this->exIndex();

        $this->_html = str_replace($header, "{% include header.htm %}", $this->_html);
        $this->_html = str_replace($nav, "{% include nav.htm %}", $this->_html);
        $this->_html = str_replace($footer, "{% include footer.htm %}", $this->_html);

    }

    public function exIndex()
    {
        print_r($this->_pos);
        // 본문을 추출합니다.

        if (isset($this->_pos['</nav']) && isset($this->_pos['</header'])) {
            // 두개의 값이 있는 경우 늦은 값 위치부터 추출합니다.
            if ( $this->_pos['</header'] > $this->_pos['</nav'] ) {
                $start = $this->_pos['</nav'];
            } else {
                $start = $this->_pos['</header'];
            }

        } else if (isset($this->_pos['</nav'])) {
            // nav 테그가 있는 경우 
            $start = $this->_pos['</nav'];

        } else if($this->_pos['</header']) {
            // header 테크가 있는 경우
            $start = $this->_pos['</header'];

        } else {
            // 값이 아무것도 없는 경우, <body 테그 부터 본문입니다.
            $start = $this->_pos['<body'];
        }

        // 본문 하단 을 체크합니다.
        if($this->_pos['<footer']){
            $end = strpos($this->_html, "<footer", 0);
        } else {
            $end = strpos($this->_html, "</body>", 0);
        }

        // 
        echo "본문은 $start ~ $end 까지 입니다.\n";
        $string = substr ($this->_html, $start, ($end-$start));        
        $this->_html = str_replace($string, "\n{% include index.htm %}\n", $this->_html);
            // 바디 저장

            $string ="---
layout: layout
---
".$string;
        file_put_contents($this->_path.$this->_theme.DS."index.htm", $string);
    }

    public function getTagBody($html, $tag)
    {
        $start = strpos($html,$tag,0);
        if($start>0){
            while(1){
                if($html[$start] == '>') {
                    // echo $html[$start];
                    $start++;
                    break;
                } else {
                    $start++;
                    continue;
                } 
            }
    
            $tagEnd = str_replace("<", "</", $tag);
            $end = strpos($html, $tagEnd, $start);
            $string = substr ($html, $start, ($end-$start));

            $this->_pos[$tag] = $start;
            $this->_pos[$tagEnd] = $end + strlen($tagEnd) + 1;

            return $string;
        }
        
    }
}