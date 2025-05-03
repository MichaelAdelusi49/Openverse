<?php
use PHPUnit\Framework\TestCase;
require_once '../frontend/public/login.php';

class AuthTest extends TestCase {
    private $auth;

    protected function setUp(): void {
        $this->auth = new Auth();
    }

    public function testLogin() {
        $this->assertTrue($this->auth->login('dan@dan.com', 'dan'));
    }

    public function testInvalidLogin() {
        $this->assertFalse($this->auth->login('wrong@example.com', 'wrongpass'));
    }
}
?>
