<?php

namespace App\Tests\Api;

use App\DataFixtures\AppFixtures;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Services\BillingService;
use App\Tests\AbstractTest;
use App\Tests\Mocks\BillingServiceMock;

class UserApiTest extends AbstractTest
{
    private function billingClient()
    {
        $this->getClient()->disableReboot();

        $this->getClient()->getContainer()->set(
            BillingService::class,
            new BillingServiceMock());

        return $this->getClient();
    }

    protected function getFixtures(): array
    {
        return [AppFixtures::class];
    }

    public function testCoursesNonAuth(){
        $client = $this->billingClient();
        $client->request('GET', '/courses');
        $this->assertResponseOk();
    }

    public function testCoursePageNonAuth(){
        $client = $this->billingClient();
        $courses = static::getContainer()->get(CourseRepository::class)->findAll();
        foreach ($courses as $course){
            $client->request('GET', "/courses/{$course->getId()}");
            $this->assertResponseOk();
        }
    }

    public function testNewCoursePageNonAuth(){
        $client = $this->billingClient();
        $client->request('GET', "/courses/new");
        $crawler = $client->followRedirect();
        $arrayedLocation = explode('/',$crawler->getUri());
        $this->assertSame('login',$arrayedLocation[3]);
    }

    public function testCourseEditPageNonAuth(){
        $client = $this->billingClient();
        $courses = static::getContainer()->get(CourseRepository::class)->findAll();
        foreach ($courses as $course){
            $client->request('GET', "/courses/{$course->getId()}/edit");
            $crawler = $client->followRedirect();
            $arrayedLocation = explode('/',$crawler->getUri());
            $this->assertSame('login',$arrayedLocation[3]);
        }
    }

    public function testLessonPageNonAuth(){
        $client = $this->billingClient();
        $lessons = static::getContainer()->get(LessonRepository::class)->findAll();
        foreach ($lessons as $lesson){
            $client->request('GET', "/lessons/{$lesson->getId()}");
            $this->assertResponseRedirect();
            $crawler = $client->followRedirect();
            $arrayedLocation = explode('/',$crawler->getUri());
            $this->assertSame('login',$arrayedLocation[3]);
        }
    }

    public function testNewLessonPageNonAuth(){
        $client = $this->billingClient();
        $client->request('GET', "/lessons/new");
        $crawler = $client->followRedirect();
        $arrayedLocation = explode('/',$crawler->getUri());
        $this->assertSame('login',$arrayedLocation[3]);
    }

    public function testEditLessonNonAuth(){
        $client = $this->billingClient();
        $lessons = static::getContainer()->get(LessonRepository::class)->findAll();
        foreach ($lessons as $lesson){
            $client->request('GET', "/lessons/{$lesson->getId()}/edit");
            $this->assertResponseRedirect();
            $crawler = $client->followRedirect();
            $arrayedLocation = explode('/',$crawler->getUri());
            $this->assertSame('login',$arrayedLocation[3]);
        }
    }

    public function testNewCoursePageOnUser(){
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Войти')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Войти')->form(
            [
                'email' => 'usualuser@study.com',
                'password' => 'user'
            ]
        );
        $client->submit($form);
        $this->assertResponseRedirect();
        $client->request('GET', "/courses/new");
        $this->assertResponseForbidden();
    }

    public function testCourseEditPageOnUser(){
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Войти')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Войти')->form(
                [
                    'email' => 'usualuser@study.com',
                    'password' => 'user'
                ]
            );
        $client->submit($form);
        $this->assertResponseRedirect();
        $courses = static::getContainer()->get(CourseRepository::class)->findAll();
        foreach ($courses as $course){
            $client->request('GET', "/courses/{$course->getId()}/edit");
            $this->assertResponseForbidden();
        }
    }

    public function testLessonPageOnUser(){
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Войти')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Войти')->form(
                [
                    'email' => 'usualuser@study.com',
                    'password' => 'user'
                ]
            );
        $client->submit($form);
        $this->assertResponseRedirect();
        $lessons = static::getContainer()->get(LessonRepository::class)->findAll();
        foreach ($lessons as $lesson){
            $crawler = $client->request('GET', "/lessons/{$lesson->getId()}");
            $this->assertResponseOk();
            $this->assertSame("{$lesson->getCourse()->getName()} / {$lesson->getName()}",
                $crawler->filter('.lesson-header')->text());
        }
    }

    public function testNewLessonPageOnUser(){
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Войти')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Войти')->form(
                [
                    'email' => 'usualuser@study.com',
                    'password' => 'user'
                ]
            );
        $client->submit($form);
        $this->assertResponseRedirect();
        $client->request('GET', "/lessons/new");
        $this->assertResponseForbidden();
    }

    public function testEditLessonOnUser(){
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Войти')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Войти')->form(
            [
                'email' => 'usualuser@study.com',
                'password' => 'user'
            ]
        );
        $client->submit($form);$this->assertResponseRedirect();
        $lessons = static::getContainer()->get(LessonRepository::class)->findAll();
        foreach ($lessons as $lesson){
            $client->request('GET', "/lessons/{$lesson->getId()}/edit");
            $this->assertResponseForbidden();
        }
    }

    public function testNewCoursePageOnAdmin(){
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Войти')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Войти')->form(
            [
                'email' => 'admin@study.com',
                'password' => 'admin'
            ]
        );
        $client->submit($form);
        $this->assertResponseRedirect();
        $client->request('GET', "/courses/new");
        $this->assertResponseOk();
    }

    public function testCourseEditPageOnAdmin(){
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Войти')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Войти')->form(
            [
                'email' => 'admin@study.com',
                'password' => 'admin'
            ]
        );
        $client->submit($form);
        $this->assertResponseRedirect();
        $courses = static::getContainer()->get(CourseRepository::class)->findAll();
        foreach ($courses as $course){
            $client->request('GET', "/courses/{$course->getId()}/edit");
            $this->assertResponseOk();
        }
    }

    public function testLessonNewPageOnAdmin(){
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Войти')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Войти')->form(
            [
                'email' => 'admin@study.com',
                'password' => 'admin'
            ]
        );
        $client->submit($form);
        $this->assertResponseRedirect();
        $courses = static::getContainer()->get(CourseRepository::class)->findAll();
        foreach ($courses as $course){
            $client->request('GET', "/lessons/new?id={$course->getId()}");
            $this->assertResponseOk();
        }
    }

    public function testLessonEditPageOnAdmin(){
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Войти')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Войти')->form(
            [
                'email' => 'admin@study.com',
                'password' => 'admin'
            ]
        );
        $client->submit($form);
        $this->assertResponseRedirect();
        $lessons = static::getContainer()->get(LessonRepository::class)->findAll();
        foreach ($lessons as $lesson){
            $client->request('GET', "/lessons/{$lesson->getId()}/edit");
            $this->assertResponseOk();
        }
    }

    public function testLogout(){
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Войти')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Войти')->form(
            [
                'email' => 'usualuser@study.com',
                'password' => 'user'
            ]
        );
        $client->submit($form);
        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();
        $link = $crawler->filter('.person-link')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $logoutLink = $crawler->filter('.logout-link')->link();
        $client->click($logoutLink);
        $crawler = $client->followRedirect();
        $this->assertResponseOk();
        $this->assertCount(1, $crawler->filter('.login-link'));
    }

    public function testSuccessfullyRegister() {
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Зарегистрироваться')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Зарегистрироваться')->form(
            [
                'form[username]' => 'test534124142124@test.com',
                'form[password][first]' => 'QWEqwe1.',
                'form[password][second]' => 'QWEqwe1.',
            ]
        );
        $client->submit($form);
        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();
        $this->assertCount(1, $crawler->filter('.person-link'));
    }

    public function testRegisterWithShortPassword() {
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Зарегистрироваться')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Зарегистрироваться')->form(
            [
                'form[username]' => 'test534124142124@test.com',
                'form[password][first]' => 'Aa1.',
                'form[password][second]' => 'Aa1.',
            ]
        );
        $client->submit($form);
        $this->assertResponseOk();
        $this->assertSelectorTextContains(
            '.invalid-feedback',
            'Пароль должен содержать минимум 6 символов.'
        );
    }

    public function testRegisterWithWrongPassword() {
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Зарегистрироваться')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Зарегистрироваться')->form(
            [
                'form[username]' => 'test534124142124@test.com',
                'form[password][first]' => 'aaaaaaaaaa',
                'form[password][second]' => 'aaaaaaaaaa',
            ]
        );
        $client->submit($form);
        $this->assertResponseOk();
        $this->assertSelectorTextContains(
            '.invalid-feedback',
            'Пароль должен содержать как один из спец. символов (.!@#$%^&*), прописную и строчные буквы латинского алфавита и цифру.'
        );
    }

    public function testRegisterWrongEmail() {
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Зарегистрироваться')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Зарегистрироваться')->form(
            [
                'form[username]' => '',
                'form[password][first]' => 'QWEqwe1.',
                'form[password][second]' => 'QWEqwe1.',
            ]
        );
        $client->submit($form);
        $this->assertResponseOk();
        $this->assertSelectorTextContains(
            '.invalid-feedback',
            'Поле e-mail содержит некорректные данные.'
        );
    }

    public function testRegisterWithExistingUserEmail() {
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Зарегистрироваться')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Зарегистрироваться')->form(
            [
                'form[username]' => 'admin@study.com',
                'form[password][first]' => 'QWEqwe1.',
                'form[password][second]' => 'QWEqwe1.',
            ]
        );
        $client->submit($form);
        $this->assertResponseOk();
        $this->assertSelectorTextContains(
            '.invalid-feedback',
            'Пользователь с таким E-mail уже зарегистрирован.'
        );
    }
}