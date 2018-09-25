# Lamp
Lamp는 지니 프레임웍의 cli툴 입니다.

## 배포

### 업데이트

로컬 파일과 서버와의 갱신 여부를 체크하여 변경된 내용만 서버로 전송을 합니다.

```php
php lamp deploy 경로

php lamp deploy:update 경로
```

### 업로드
업로드는 서버 소스 파일을 체크하지 않고 로컬의 파일을 서버로 덥어쓰게 됩니다.

```php
php lamp deploy:upload 경로
```

