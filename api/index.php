<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function reply(array $data, int $status = 200): never { http_response_code($status); echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR); exit; }
$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
if (!in_array($action, ['progress','lesson','quiz','reset'], true)) reply(['error'=>'Geçersiz işlem.'],404);
if (($action==='progress' && $method!=='GET') || ($action!=='progress' && $method!=='POST')) reply(['error'=>'Geçersiz yöntem.'],405);
$configPath = getenv('YORUNGE_CONFIG') ?: dirname(__DIR__,2).'/yorunge-config.php';
if (!is_file($configPath)) reply(['error'=>'Veritabanı henüz yapılandırılmadı.'],503);
try {
$config = require $configPath;
$origin = rtrim($config['origin'] ?? '', '/');
if (!preg_match('~^https://[^/]+$~',$origin)) reply(['error'=>'Sunucu yapılandırması eksik.'],503);
if ($method==='POST' && ($_SERVER['HTTP_ORIGIN'] ?? '') !== $origin) reply(['error'=>'Geçersiz kaynak.'],403);
ini_set('session.use_strict_mode','1');
session_name('yorunge_session');
session_set_cookie_params(['lifetime'=>0,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
if ($method==='POST' && !hash_equals($_SESSION['csrf'],$_SERVER['HTTP_X_CSRF_TOKEN']??'')) reply(['error'=>'Sayfayı yenileyip tekrar deneyin.'],403);
// An opaque, signed device cookie keeps progress across PHP sessions. No names or email addresses.
$key=$config['app_key']??'';
if(strlen($key)<32) reply(['error'=>'Sunucu anahtarı eksik.'],503);
$cookie=explode('.',$_COOKIE['yorunge_device']??'');
$device=(count($cookie)===2 && preg_match('/^[a-f0-9]{64}$/',$cookie[0]) && hash_equals(hash_hmac('sha256',$cookie[0],$key),$cookie[1])) ? $cookie[0] : bin2hex(random_bytes(32));
setcookie('yorunge_device',$device.'.'.hash_hmac('sha256',$device,$key),['expires'=>time()+31536000,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
$pdo=new PDO($config['dsn'],$config['user'],$config['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
$pdo->prepare('INSERT IGNORE INTO learners (id) VALUES (?)')->execute([$device]);
if($action==='progress'){
 $q=$pdo->prepare('SELECT lesson_id FROM lessons WHERE learner_id=? ORDER BY lesson_id');$q->execute([$device]);$seen=$q->fetchAll(PDO::FETCH_COLUMN);
 $q=$pdo->prepare('SELECT COALESCE(MAX(score),0) FROM attempts WHERE learner_id=?');$q->execute([$device]);
 reply(['seen'=>$seen,'best'=>(int)$q->fetchColumn(),'csrf'=>$_SESSION['csrf']]);
}
if((int)($_SERVER['CONTENT_LENGTH']??0)>4096)reply(['error'=>'İstek çok büyük.'],413);
$raw=file_get_contents('php://input',false,null,0,4097);if(strlen($raw)>4096)reply(['error'=>'İstek çok büyük.'],413);
try{$body=json_decode($raw,true,16,JSON_THROW_ON_ERROR);}catch(JsonException){reply(['error'=>'Geçersiz veri.'],400);}
if(!is_array($body))reply(['error'=>'Geçersiz veri.'],400);
if($action==='lesson'){
 $valid=['sun','mercury','venus','earth','mars','jupiter','saturn','uranus','neptune','layers-sun','layers-earth','phases'];
 if(!in_array($body['id']??null,$valid,true))reply(['error'=>'Geçersiz konu.'],422);
 $pdo->prepare('INSERT IGNORE INTO lessons (learner_id,lesson_id) VALUES (?,?)')->execute([$device,$body['id']]);reply(['saved'=>true]);
}
if($action==='quiz'){
 $questions=json_decode(file_get_contents(__DIR__.'/../assets/questions.json'),true,32,JSON_THROW_ON_ERROR);
 $answers=$body['answers']??null;
 if(!is_array($answers)||!array_is_list($answers)||count($answers)!==count($questions))reply(['error'=>'Tüm soruları yanıtlayın.'],422);
 $score=0;foreach($questions as $i=>$q){if(!is_int($answers[$i])||$answers[$i]<0||$answers[$i]>3)reply(['error'=>'Geçersiz cevap.'],422);if($answers[$i]===$q['answer'])$score+=10;}
 $q=$pdo->prepare('SELECT COUNT(*) FROM attempts WHERE learner_id=? AND created_at > NOW() - INTERVAL 1 HOUR');$q->execute([$device]);
 if((int)$q->fetchColumn()>=30)reply(['error'=>'Biraz ara verip yeniden deneyin.'],429);
 $pdo->prepare('INSERT INTO attempts (learner_id,score,answers) VALUES (?,?,?)')->execute([$device,$score,json_encode($answers)]);reply(['saved'=>true,'score'=>$score]);
}
if($action==='reset'){
 $pdo->prepare('DELETE FROM learners WHERE id=?')->execute([$device]);
 setcookie('yorunge_device','',['expires'=>time()-3600,'path'=>'/','secure'=>true,'httponly'=>true,'samesite'=>'Lax']);
 session_destroy();reply(['deleted'=>true]);
}
}catch(Throwable $e){error_log('Yorunge API: '.$e->getMessage());reply(['error'=>'Kayıt hizmetine şu anda ulaşılamıyor.'],503);}
