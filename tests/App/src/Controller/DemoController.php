<?php

declare(strict_types=1);

namespace Ep\Tests\App\Controller;

use DateInterval;
use Ep;
use Ep\Attribute\Inject;
use Ep\Auth\AuthRepository;
use Ep\Auth\Method\HttpSession;
use Ep\Db\ActiveQuery;
use Ep\Db\Query;
use Ep\Helper\Str;
use Ep\Kit\Crypt;
use Ep\Tests\App\Component\Controller;
use Ep\Tests\App\Facade\Cache as FacadeCache;
use Ep\Tests\App\Facade\Logger;
use Ep\Tests\App\Model\Student;
use Ep\Tests\Support\Object\Animal\Bird;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Cache\Cache;
use Yiisoft\Cookies\Cookie;
use Yiisoft\Cookies\CookieCollection;
use Yiisoft\Db\Connection\ConnectionInterface;
use Yiisoft\Factory\Factory;
use Yiisoft\Session\SessionInterface;

class DemoController extends Controller
{
    #[Inject]
    private CacheInterface $cache;
    private ConnectionInterface $db;

    public function __construct()
    {
        $this->db = Ep::getDb('sqlite');
    }

    public function index()
    {
        return $this->string('<h1>hello world</h1>');
    }

    public function downloadFile(ServerRequestInterface $request, Aliases $aliases)
    {
        // $name = 'eye.png';
        $name = 'face.jpg';
        $file = $aliases->get('@root/static/image/' . $name);

        $newName = null;
        // $newName = '0!§ $&()=`´{}  []²³@€µ^°_+\' # - _ . , ; ü ä ö ß 9.jpg';

        return $this->download($request, $file, $newName);
    }

    public function request(ServerRequestInterface $request)
    {
        $result = [
            'method' => $request->getMethod(),
            'get' => $request->getQueryParams(),
            'post' => $request->getParsedBody(),
            'raw' => $request->getBody()->getContents(),
            'cookie' => $request->getCookieParams(),
            'host' => $request->getUri()->getHost(),
            'header' => $request->getHeaders(),
            'path' => $request->getUri()->getPath()
        ];
        return $this->json($result);
    }

    public function redirectUrl(ServerRequestInterface $request)
    {
        $id = Student::find($this->db)->orderBy('id DESC')->select('id')->scalar();

        return $this->redirect('arform?id=' . $id);
    }

    public function log(LoggerInterface $logger)
    {
        $logger->info(sprintf('%s logged', __METHOD__));
        return $this->string('logged');
    }

    public function cache(Cache $cache)
    {
        $r = $cache->getOrSet('name', fn (): int => mt_rand(0, 100), 5);

        return $this->string((string) $r);
    }

    public function save()
    {
        $user = new Student($this->db);
        $user->username = '路人甲' . mt_rand(0, 100);
        $user->class_id = 3;
        $user->age = mt_rand(0, 100);
        $r1 = $user->insert();


        $user = Student::findModel(1, $this->db);
        $user->desc = 'desc has been updated' . mt_rand(0, 100);
        $r2 = $user->update();

        return $this->json(compact('r1', 'r2'));
    }

    public function query()
    {
        $result = [];
        /** @var ActiveQuery */
        $query = Student::find($this->db)
            ->joinWith('class')
            ->andWhere(['class.id' => 3]);
        $result['RawSql'] = $query->getRawSql();
        $user = $query->one();
        if ($user) {
            $result['Model Attributes'] = $user->getAttributes();
        }
        $result['Count'] = $query->count();
        $list = $query->asArray()->all();
        $result['All'] = $list;

        return $this->json($result);
    }

    public function curd()
    {
        $insert = 0;
        $update = 0;
        $batchInsert = 0;
        $upsert = 0;
        $delete = 0;

        $insert = Query::find($this->db)->insert('student', [
            'class_id' => 1,
            'password' => mt_rand(),
            'name' => '路人乙' . mt_rand(0, 100)
        ]);
        $insert = Query::find($this->db)->insert('student', Query::find($this->db)->from('student')->select(['name', 'class_id', 'password', 'age'])->where(['id' => 1]));
        $update =  Query::find($this->db)->update('student', ['desc' => 'code: ' . mt_rand()], 'id=:id', [':id' => 2]);

        $batchInsert = Query::find($this->db)->batchInsert('student', ['name', 'class_id', 'password', 'age'], [
            ['a1', mt_rand(1, 10), mt_rand(), 11],
            ['b1', mt_rand(1, 10), mt_rand(), 22],
            ['c1', mt_rand(1, 10), mt_rand(), 33],
        ]);
        $upsert = Query::find($this->db)->upsert('student', ['id' => 72, 'name' => 'julia', 'age' => 99], ['age' => 33]);
        $delete = Query::find($this->db)->delete('student', ['id' => 75]);

        $increment = Query::find($this->db)->increment('student', ['age' => -1, 'name' => 'peter'], 'id=:id', [':id' => 9]);

        return $this->json(compact('insert', 'update', 'batchInsert', 'upsert', 'delete', 'increment'));
    }

    public function event(EventDispatcherInterface $dipatcher)
    {
        $dipatcher->dispatch($this);

        return $this->string();
    }

    public function crypt(Crypt $crypt)
    {
        $text = Str::random();
        $pwd1 = $crypt->encrypt($text);
        $parse1 = $crypt->decrypt($pwd1);

        $crypt = $crypt->withMethod('AES-256-CBC', 'hclOAajQ5DRIpgyLeXP4GfZRvSPNXfGh9fVxHWwCTyg=');
        $pwd2 = $crypt->encrypt($text);
        $parse2 = $crypt->decrypt($pwd2);

        return $this->json([
            'text' => $text,
            '128' => compact('pwd1', 'parse1'),
            '256' => compact('pwd2', 'parse2'),
        ]);
    }

    public function validate()
    {
        $user = Student::findModel(1, $this->db);
        $r = $user->validate();
        if ($r) {
            return $this->string('validate ok');
        } else {
            return $this->json($user->getErrors());
        }
    }

    public function getUser()
    {
        $data = Student::find($this->db)
            ->joinWith('class')
            ->asArray()
            ->one();

        return $this->success($data);
    }

    public function arform(ServerRequestInterface $request)
    {
        $student = Student::findModel($request->getQueryParams()['id'] ?? 0, $this->db);
        if ($student->load($request)) {
            $student->class_id = 1;
            $trans = $this->db->beginTransaction();
            if (!$student->validate()) {
                return $this->error($student->getErrors());
            }
            if ($student->save()) {
                $trans->commit();
                return $this->success();
            } else {
                $trans->rollBack();
                return $this->error($student->getErrors());
            }
        }
        return $this->render('arform', compact('student'));
    }

    public function ws()
    {
        return $this->render('ws');
    }

    public function getCookie(ServerRequestInterface $request)
    {
        $cookies = CookieCollection::fromArray($request->getCookieParams());

        return $this->json([
            'testcookie' => $cookies->getValue('testcookie')
        ]);
    }

    public function setCookie()
    {
        $cookie = new Cookie('testcookie', 'testcookie' . mt_rand());
        $cookie = $cookie->withMaxAge(new DateInterval('PT10S'))->withSecure(false);

        $cookie2 = new Cookie('testcookie2', 'testcookie2' . mt_rand());
        $cookie2 = $cookie2->withMaxAge(new DateInterval('PT20S'))->withSecure(false);

        $response = $this->string('ok');
        $response = $response->withAddedHeader('t1', 'v1');
        $response = $response->withAddedHeader('t1', 'v2');
        $response = $response->withAddedHeader('t1', 'v3');

        $response = $response->withHeader('z1', 'v1');
        $response = $response->withHeader('z1', 'v2');
        $response = $response->withHeader('z1', 'v3');

        return $cookie->addToResponse($cookie2->addToResponse($response));
    }

    public function session(SessionInterface $session)
    {
        $session->set('title', 'sessionTest');

        $r = $session->get('title');

        return $this->json($r);
    }

    public function paginate(ServerRequestInterface $request)
    {
        $page = (int) ($request->getQueryParams()['page'] ?? 1);
        $query = Student::find($this->db)->asArray();
        $count = $query->count();

        return $this->json([
            'count' => $count,
            'next' => $query->nextPage($page, 3),
            'data' => $query->getPaginator()->data($page, 3),
            'all' => $query->getPaginator()->all($page, 3)
        ]);
    }

    public function facade()
    {
        FacadeCache::set('a', 123);
        $r = FacadeCache::get('a');
        Logger::alert('alaa');
        $alert = Ep::getLogger('alert');
        Logger::swap($alert);
        Logger::alert('i am alert');
        Logger::clear();
        Logger::alert('i am reset');

        return $this->string($r);
    }

    public function login(ServerRequestInterface $request, SessionInterface $session, AuthRepository $auth)
    {
        $p = $request->getQueryParams();
        $username = $p['u'] ?? '';
        $password = $p['p'] ?? '';
        if (!$username || !$password) {
            return $this->error('require params u or p');
        }

        $user = Query::find($this->db)->from('student')->where([
            'name' => $username,
            'password' => $password
        ])->one();
        if (!$user) {
            return $this->error('missing user');
        }
        $method = $auth->findMethod('frontend');
        if ($method instanceof HttpSession) {
            $session->set($method->getId(), $user['id']);
        } else {
            return $this->error('Wrong auth method');
        }

        return $this->string('Logined');
    }

    public function factory(Factory $factory)
    {
        $bird1 = $factory->create(Bird::class);

        $bird2 = $factory->create(Bird::class);

        return $this->json([
            'ref' => $bird1 === $bird2,
            'name' => get_class($bird1) === Bird::class,
            'value' => $bird1->getSpeed() === 30
        ]);
    }

    public function test()
    {
        return $this->string();
    }
}
