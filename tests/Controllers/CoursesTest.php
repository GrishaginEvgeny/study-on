<?php

namespace App\Tests\Controllers;

use App\DataFixtures\AppFixtures;
use App\DataFixtures\UserFixtures;
use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Services\BillingCoursesService;
use App\Services\BillingUserService;
use App\Tests\AbstractTest;
use App\Tests\Mocks\BillingCourseServiceMock;
use App\Tests\Mocks\BillingUserServiceMock;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CoursesTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [AppFixtures::class];
    }

    private function billingClient()
    {
        $this->getClient()->disableReboot();

        $this->getClient()->getContainer()->set(
            BillingUserService::class,
            new BillingUserServiceMock()
        );

        $this->getClient()->getContainer()->set(
            BillingCoursesService::class,
            new BillingCourseServiceMock()
        );

        return $this->getClient();
    }

    public function testNewCoursePage()
    {
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
        $client->request('GET', '/courses/new');
        $this->assertResponseOk();
    }

    public function testCoursesPage()
    {
        $client = $this->billingClient();
        $client->request('GET', "/courses");
        $this->assertResponseOk();
    }

    public function testNumberOfCourses()
    {
        $client = $this->billingClient();
        $crawler = $client->request('GET', "/courses");
        $countOnDB = count(static::getContainer()->get(CourseRepository::class)->findAll());
        $this->assertCount($countOnDB, $crawler->filter('.course-card'));
    }

    public function testNotExistedCourseSuccessfully()
    {
        $client = $this->billingClient();
        $client->request('GET', "/courses/-413241");
        $this->assertResponseNotFound();
    }

    public function testCourseAdding()
    {
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
        $countBefore = count(static::getContainer()->get(CourseRepository::class)->findAll());
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();
        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[CharacterCode]' => 'test',
                'course[Name]' => 'test',
                'course[Description]' => 'test',
            ]
        );
        $client->submit($form);

        $addedCourse = static::getContainer()->get(CourseRepository::class)->findOneBy(['CharacterCode' => 'test']);
        $countAfter = count(static::getContainer()->get(CourseRepository::class)->findAll());
        $this->assertNotNull($addedCourse);
        $this->assertEquals($countBefore + 1, $countAfter);

        $crawler = $client->followRedirect();
        $this->assertResponseOk();


        $this->assertCount($countAfter, $crawler->filter('.course-card'));
    }

    public function testAddCourseWithEmptyCharacterCode()
    {
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
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();

        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[CharacterCode]' => '',
                'course[Name]' => 'test',
                'course[Description]' => 'test',
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
        $this->assertSelectorTextContains('.invalid-feedback.d-block',
            'Поле "Cимвольный код" не должно быть пустым.');
    }

    public function testAddCourseWithWrongCharacterCode()
    {
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
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();

        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[CharacterCode]' => '^&#$##@fdad',
                'course[Name]' => 'test',
                'course[Description]' => 'test',
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
        $this->assertSelectorTextContains('.invalid-feedback.d-block',
            'В поле "Cимвольный код" могут содержаться только цифры и латиница.');
    }

    public function testAddCourseWithEmptyName()
    {
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
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();

        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[CharacterCode]' => 'php',
                'course[Name]' => '',
                'course[Description]' => 'test',
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
        $this->assertSelectorTextContains('.invalid-feedback.d-block',
            'Поле "Название" не должно быть пустым.');
    }

    public function testAddCourseWithNotUniqueCharacterCode()
    {
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
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();

        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[CharacterCode]' => static::getContainer()
                    ->get(CourseRepository::class)
                    ->findAll()[0]
                    ->getCharacterCode(),
                'course[Name]' => 'test',
                'course[Description]' => 'test',
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
        $this->assertSelectorTextContains('.invalid-feedback.d-block',
            'Курс с таким символьным кодом уже существует.');
    }

    public function testAddCourseWithCharacterCodeLengthGreaterThanConstraint()
    {
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
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();

        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[CharacterCode]' => str_repeat("a", 256),
                'course[Name]' => 'test',
                'course[Description]' => 'test',
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
        $this->assertSelectorTextContains('.invalid-feedback.d-block',
            'Поле "Cимвольный код" не должно быть длинной более 255 символов.');
    }

    public function testAddCourseWithNameLengthGreaterThanConstraint()
    {
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
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();

        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[CharacterCode]' => 'test',
                'course[Name]' => str_repeat("a", 256),
                'course[Description]' => 'test',
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
        $this->assertSelectorTextContains('.invalid-feedback.d-block',
            'Поле "Название" не должно быть длинной более 255 символов.');
    }

    public function testAddCourseWithDescriptionLengthGreaterThanConstraint()
    {
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
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();

        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[CharacterCode]' => 'test',
                'course[Name]' => 'test',
                'course[Description]' => str_repeat("a", 1001),
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
        $this->assertSelectorTextContains('.invalid-feedback.d-block',
            'Поле "Описание" не должно быть длинной более 1000 символов.');
    }

    public function testEditCourse()
    {
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
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();

        $courseLink = $crawler->filter('.link_to_course')->link();
        $crawler = $client->click($courseLink);
        $this->assertResponseOk();

        $editCourseLink = $crawler->filter('.edit_course_link')->link();
        $crawler = $client->click($editCourseLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Обновить')->form(
            [
                'course[CharacterCode]' => 'test123',
                'course[Name]' => 'testName',
                'course[Description]' => 'test',
            ]
        );
        $client->submit($form);

        $editedCourse = static::getContainer()->get(CourseRepository::class)->findOneBy(['CharacterCode' => 'test123']);
        $this->assertNotNull($editedCourse);
        $crawler = $client->request('GET', "/courses/{$editedCourse->getId()}");
        $this->assertResponseOk();
        $this->assertSame('testName', $crawler->filter('.course_page_header')->text());
    }

    public function testRemoveCourse()
    {
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
        $crawler = $client->request('GET', '/courses');
        $countBefore = count(static::getContainer()->get(CourseRepository::class)->findAll());
        $this->assertResponseOk();

        $courseLink = $crawler->filter('.link_to_course')->link();
        $client->click($courseLink);
        $this->assertResponseOk();

        $client->submitForm('Удалить курс');
        $countAfter = count(static::getContainer()->get(CourseRepository::class)->findAll());
        $this->assertSame($countAfter, $countBefore - 1);
        $crawler = $client->followRedirect();
        $this->assertCount($countAfter, $crawler->filter('.course-card'));
    }
}
