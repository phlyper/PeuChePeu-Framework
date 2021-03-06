<?php
namespace Tests\App\Auth;

use App\Auth\AuthService;
use App\Auth\Entity\User;
use App\Auth\Table\UserTable;
use Framework\Database\Database;
use Framework\Session\Session;

class AuthServiceTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var AuthService
     */
    private $auth;

    /**
     * @var User
     */
    private $user;

    /**
     * @var \Framework\Session\Session
     */
    private $session;

    /**
     * @var PHPUnit_Framework_MockObject_MockObject
     */
    private $table;

    public function setUp()
    {
        $this->session = new Session();
        $this->user = new User();
        $this->user->id = 3;
        $this->user->password = password_hash('0000', PASSWORD_DEFAULT);
        $this->table = $this
            ->getMockBuilder(UserTable::class)
            ->setConstructorArgs([new Database('fake')])
            ->setMethods(['findByUsername', 'find'])
            ->getMock();
        $this->auth = new AuthService($this->table, $this->session);
    }

    public function tearDown()
    {
        $this->session->destroy();
    }

    public function testLoginWithNull () {
        $this->assertEquals(null, $this->auth->login(null, null));
    }

    public function testLogin()
    {
        $this->table->expects($this->once())
            ->method('findByUsername')
            ->with('John')
            ->will($this->returnValue($this->user));
        $this->assertEquals($this->user, $this->auth->login('John', '0000'));
    }

    public function testLoginWithBadPassword()
    {
        $this->table->expects($this->once())
            ->method('findByUsername')
            ->with('John')
            ->will($this->returnValue($this->user));
        $this->assertEquals(null, $this->auth->login('John', '0010'));
    }

    public function testLoginWithBadUsername()
    {
        $this->table->expects($this->once())
            ->method('findByUsername')
            ->with('John')
            ->will($this->returnValue(null));
        $this->assertEquals(null, $this->auth->login('John', '0000'));
    }

    public function testRetrieveUserFromDatabase()
    {
        $this->testLogin();
        $this->table->expects($this->once())
            ->method('find')
            ->with($this->user->id)
            ->will($this->returnValue($this->user));
        $this->assertEquals($this->user, $this->auth->user());
    }

    public function testDatabaseShouldBeCalledOnce()
    {
        $this->testLogin();
        $this->table->expects($this->once())
            ->method('find')
            ->will($this->returnValue($this->user));
        $this->auth->user();
        $this->auth->user();
    }

    public function testRetrieveNoUser()
    {
        $this->testLogin();
        $this->table->expects($this->once())
            ->method('find')
            ->with($this->user->id)
            ->will($this->returnValue(null));
        $this->assertEquals(null, $this->auth->user());
    }

    public function testUserWithNothingInSession()
    {
        $this->assertEquals(null, $this->auth->user());
    }

    public function testBadLoginShouldDestroySession()
    {
        $this->testLogin();
        $this->assertEquals($this->user->id, $this->session->get('auth.user'));
        $this->table->expects($this->once())
            ->method('find')
            ->with($this->user->id)
            ->will($this->returnValue(null));
        $this->auth->user();
        $this->assertEquals(null, $this->session->get('auth.user'));
    }

}