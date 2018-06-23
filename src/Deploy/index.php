<?php

include "component.php";
include "composite.php";
include "leaf.php";

echo "Composite Pattern <br>";

// 폴더
$root = new Composite("root");
    $home = new Composite("home");
        $hojin = new Composite("hojin");
        $jiny = new Composite("jiny");
    $users = new Composite("user");
    $temp = new Composite("temp");

// 파일
$img1 = new Leaf("img1");
$img2 = new Leaf("img2");
$img3 = new Leaf("img3");
$img4 = new Leaf("img4");

// 
// 상단에 서브 컴포넌트(폴더)를 추가합니다.
$root->addNode($home);
$root->addNode($users);
    // 서브폴더를 추가
    $users->addNode($hojin);
        // 파일(leaf)추가
        $hojin->addNode($img1);
        $hojin->addNode($img2);
        $hojin->addNode($img3);
        $hojin->addNode($img4);
    $users->addNode($jiny);
$root->addNode($temp);

// echo "<pre>";    
// var_dump($root);
// echo "</pre>";

function show($component) {
    $arr = $component->children;
    foreach ($arr as $key => $value) {
        
        if ($value instanceof Composite) {
            echo "Composite = ". $key. "<br>";

        } else if ($value instanceof Leaf) {
            echo "Leaf = ". $key. "<br>";
            
        } else {
            echo "??<br>";
        }

        // 재귀호출 탐색
        if ($value) show($value);

    }
}

// 컴포짓 노트 트리를 출력합니다.
show($root);
