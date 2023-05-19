<?php

namespace App\Tests\Security;

use App\DataFixtures\AppFixtures;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Services\BillingCoursesService;
use App\Services\BillingUserService;
use App\Tests\AbstractTest;
use App\Tests\Mocks\BillingCourseServiceMock;
use App\Tests\Mocks\BillingUserServiceMock;

class UserTest extends AbstractTest
{
    private function billingClient()
    {
        $this->getClient()->disableReboot();

        $translator = static::getContainer()->get('translator');

        $this->getClient()->getContainer()->set(
            BillingUserService::class,
            new BillingUserServiceMock($translator)
        );

        $this->getClient()->getContainer()->set(
            BillingCoursesService::class,
            new BillingCourseServiceMock($translator)
        );

        return $this->getClient();
    }

    private function adminLogin(?\Symfony\Component\BrowserKit\AbstractBrowser $client)
    {
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
    }

    private function userLogin(?\Symfony\Component\BrowserKit\AbstractBrowser $client)
    {
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
    }

    protected function getFixtures(): array
    {
        return [AppFixtures::class];
    }

    public function testCoursesNonAuth()
    {
        $client = $this->billingClient();
        $client->request('GET', '/courses');
        $this->assertResponseOk();
    }

    public function testCoursePageNonAuth()
    {
        $client = $this->billingClient();
        $courses = static::getContainer()->get(CourseRepository::class)->findAll();
        foreach ($courses as $course) {
            $client->request('GET', "/courses/{$course->getId()}");
            $this->assertResponseOk();
        }
    }

    public function testNewCoursePageNonAuth()
    {
        $client = $this->billingClient();
        $client->request('GET', "/courses/new");
        $crawler = $client->followRedirect();
        $arrayedLocation = explode('/', $crawler->getUri());
        $this->assertSame('login', $arrayedLocation[3]);
    }

    public function testCourseEditPageNonAuth()
    {
        $client = $this->billingClient();
        $courses = static::getContainer()->get(CourseRepository::class)->findAll();
        foreach ($courses as $course) {
            $client->request('GET', "/courses/{$course->getId()}/edit");
            $crawler = $client->followRedirect();
            $arrayedLocation = explode('/', $crawler->getUri());
            $this->assertSame('login', $arrayedLocation[3]);
        }
    }

    public function testLessonPageNonAuth()
    {
        $client = $this->billingClient();
        $lessons = static::getContainer()->get(LessonRepository::class)->findAll();
        foreach ($lessons as $lesson) {
            $client->request('GET', "/lessons/{$lesson->getId()}");
            $this->assertResponseRedirect();
            $crawler = $client->followRedirect();
            $arrayedLocation = explode('/', $crawler->getUri());
            $this->assertSame('login', $arrayedLocation[3]);
        }
    }

    public function testNewLessonPageNonAuth()
    {
        $client = $this->billingClient();
        $client->request('GET', "/lessons/new");
        $crawler = $client->followRedirect();
        $arrayedLocation = explode('/', $crawler->getUri());
        $this->assertSame('login', $arrayedLocation[3]);
    }

    public function testEditLessonNonAuth()
    {
        $client = $this->billingClient();
        $lessons = static::getContainer()->get(LessonRepository::class)->findAll();
        foreach ($lessons as $lesson) {
            $client->request('GET', "/lessons/{$lesson->getId()}/edit");
            $this->assertResponseRedirect();
            $crawler = $client->followRedirect();
            $arrayedLocation = explode('/', $crawler->getUri());
            $this->assertSame('login', $arrayedLocation[3]);
        }
    }

    public function testNewCoursePageOnUser()
    {
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

    public function testCourseEditPageOnUser()
    {
        $client = $this->billingClient();
        $this->userLogin($client);
        $courses = static::getContainer()->get(CourseRepository::class)->findAll();
        foreach ($courses as $course) {
            $client->request('GET', "/courses/{$course->getId()}/edit");
            $this->assertResponseForbidden();
        }
    }

    public function testLessonPageOnUser()
    {
        $client = $this->billingClient();
        $this->userLogin($client);
        $lessons = static::getContainer()->get(LessonRepository::class)->findAll();
        foreach ($lessons as $lesson) {
            $crawler = $client->request('GET', "/lessons/{$lesson->getId()}");
            $this->assertResponseOk();
            $this->assertSame(
                "{$lesson->getCourse()->getName()} / {$lesson->getName()}",
                $crawler->filter('.lesson-header')->text()
            );
        }
    }

    public function testNewLessonPageOnUser()
    {
        $client = $this->billingClient();
        $this->userLogin($client);
        $client->request('GET', "/lessons/new");
        $this->assertResponseForbidden();
    }

    public function testEditLessonOnUser()
    {
        $client = $this->billingClient();
        $this->userLogin($client);
        $lessons = static::getContainer()->get(LessonRepository::class)->findAll();
        foreach ($lessons as $lesson) {
            $client->request('GET', "/lessons/{$lesson->getId()}/edit");
            $this->assertResponseForbidden();
        }
    }

    public function testNewCoursePageOnAdmin()
    {
        $client = $this->billingClient();
        $this->adminLogin($client);
        $client->request('GET', "/courses/new");
        $this->assertResponseOk();
    }

    public function testCourseEditPageOnAdmin()
    {
        $client = $this->billingClient();
        $this->adminLogin($client);
        $courses = static::getContainer()->get(CourseRepository::class)->findAll();
        foreach ($courses as $course) {
            $client->request('GET', "/courses/{$course->getId()}/edit");
            $this->assertResponseOk();
        }
    }

    public function testLessonNewPageOnAdmin()
    {
        $client = $this->billingClient();
        $this->adminLogin($client);
        $courses = static::getContainer()->get(CourseRepository::class)->findAll();
        foreach ($courses as $course) {
            $client->request('GET', "/lessons/new?id={$course->getId()}");
            $this->assertResponseOk();
        }
    }

    public function testLessonEditPageOnAdmin()
    {
        $client = $this->billingClient();
        $this->adminLogin($client);
        $lessons = static::getContainer()->get(LessonRepository::class)->findAll();
        foreach ($lessons as $lesson) {
            $client->request('GET', "/lessons/{$lesson->getId()}/edit");
            $this->assertResponseOk();
        }
    }

    public function testLogout()
    {
        $client = $this->billingClient();
        $this->userLogin($client);
        $crawler = $client->followRedirect();
        $link = $crawler->filter('.person-link')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $logoutLink = $crawler->filter('.logout-link')->link();
        $client->click($logoutLink);
        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();
        $this->assertResponseOk();
        $this->assertCount(1, $crawler->filter('.login-link'));
    }

    public function testSuccessfullyRegister()
    {
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Зарегистрироваться')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Зарегистрироваться')->form(
            [
                'register[username]' => 'test534124142124@test.com',
                'register[password][first]' => 'QWEqwe1.',
                'register[password][second]' => 'QWEqwe1.',
            ]
        );
        $client->submit($form);
        $this->assertResponseRedirect();
        $crawler = $client->followRedirect();
        $this->assertCount(1, $crawler->filter('.person-link'));
    }

    public function testRegisterWithShortPassword()
    {
        $client = $this->billingClient();
        $translator = static::getContainer()->get('translator');
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Зарегистрироваться')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Зарегистрироваться')->form(
            [
                'register[username]' => 'test534124142124@test.com',
                'register[password][first]' => 'Aa1.',
                'register[password][second]' => 'Aa1.',
            ]
        );
        $client->submit($form);
        $this->assertResponseOk();
        $this->assertSelectorTextContains(
            '.invalid-feedback',
            $translator->trans('errors.register.password.too_tiny', [], 'validators')
        );
    }

    public function testRegisterWithWrongPassword()
    {
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $translator = static::getContainer()->get('translator');
        $link = $crawler->selectLink('Зарегистрироваться')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Зарегистрироваться')->form(
            [
                'register[username]' => 'test534124142124@test.com',
                'register[password][first]' => 'aaaaaaaaaa',
                'register[password][second]' => 'aaaaaaaaaa',
            ]
        );
        $client->submit($form);
        $this->assertResponseOk();
        $this->assertSelectorTextContains(
            '.invalid-feedback',
            $translator->trans('errors.register.password.wrong_regex', [], 'validators')
        );
    }

    public function testRegisterWrongEmail()
    {
        $client = $this->billingClient();
        $translator = static::getContainer()->get('translator');
        $crawler = $client->request('GET', '/courses');
        $link = $crawler->selectLink('Зарегистрироваться')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Зарегистрироваться')->form(
            [
                'register[username]' => '',
                'register[password][first]' => 'QWEqwe1.',
                'register[password][second]' => 'QWEqwe1.',
            ]
        );
        $client->submit($form);
        $this->assertResponseOk();
        $this->assertSelectorTextContains(
            '.invalid-feedback',
            $translator->trans('errors.register.email.non_empty', [], 'validators')
        );
    }

    public function testRegisterWithExistingUserEmail()
    {
        $client = $this->billingClient();
        $crawler = $client->request('GET', '/courses');
        $translator = static::getContainer()->get('translator');
        $link = $crawler->selectLink('Зарегистрироваться')->link();
        $crawler = $client->click($link);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Зарегистрироваться')->form(
            [
                'register[username]' => 'admin@study.com',
                'register[password][first]' => 'QWEqwe1.',
                'register[password][second]' => 'QWEqwe1.',
            ]
        );
        $client->submit($form);
        $this->assertResponseOk();
        $this->assertSelectorTextContains(
            '.alert',
            $translator->trans('errors.register.email.non_unique', [], 'validators')
        );
    }
}
