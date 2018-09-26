# Lamp Cli
본 코드는 `PHP`언어로 작성된 `composer` 페키지 입니다. 또한 `jinyPHP` 프레임워크와 같이 동작을 합니다.
지니PHP는 MVC 패턴의 웹프레임워크 입니다.


## 설치방법
composer를 통하여 설치를 진행할 수 있습니다.

```php
composer require jiny/lamp
```


## 소스경로
모든 코드는 깃허브 저장소에 공개되어 있습니다.
https://github.com/jinyphp/lamp

누구나 코드를 포크하여 수정 및 개선사항을 기여(contrubution)할 수 있습니다.


## CLI
Lamp는 지니PHP의 cLI 도구의 이름입니다. 램프는 몇가지의 관리 기능을 제공합니다.

### 배포
로컬작업물을 서버로 배포합니다. 배포 방식은 ftp를 사용합니다.
배포시 로컬과 서버간의 파일 갱신 여부를 체크하여 변경된 내용만 서버로 전송을 합니다.  

```php
php lamp deploy 경로
php lamp deploy:update 경로
```

업로드는 서버 소스 파일을 체크하지 않고 로컬의 파일을 서버로 덥어쓰게 됩니다.

```php
php lamp deploy:upload 경로
```

### FTP
별도의 클라이언트 도구없이 ftp 원격작업을 하실 수 있습니다.

### 테마
외부 웹사이트로 부터 크롤링하여 테마를 생성할 수 있습니다.
완벽하지는 않습니다.