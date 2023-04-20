<?php

namespace App\Tests\Controllers;

use App\DataFixtures\UserFixtures;
use App\Entity\Course;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Tests\AbstractTest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CoursesTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [UserFixtures::class];
    }

    public function urlProviderForSuccessGETRequests(): \Generator
    {
        yield ['/courses'];
        yield ['/courses/new'];
    }

    /**
     * @dataProvider urlProviderForSuccessGETRequests
     */
    public function testPagesOnGETRequests($url): void
    {
        $this->getClient()->request('GET', $url);
        $this->assertResponseOk();
    }

    public function testCoursePagesOnGETRequest()
    {
        $client = $this->getClient();
        $courses = static::getContainer()->get(CourseRepository::class)->findAll();
        foreach ($courses as $course) {
            $client->request('GET', "/courses/{$course->getId()}/edit");
            $this->assertResponseOk();

            $client->request('GET', "/courses/{$course->getId()}");
            $this->assertResponseOk();
        }
    }

    public function testCoursePagesOnPOSTRequest()
    {
        $client = $this->getClient();
        $client->request('POST', "/courses/new");
        $this->assertResponseOk();
        $courses = static::getContainer()->get(CourseRepository::class)->findAll();
        foreach ($courses as $course) {
            $client->request('POST', "/courses/{$course->getId()}/edit");
            $this->assertResponseOk();

            $client->request('POST', "/courses/{$course->getId()}");
            $this->assertResponseRedirect();
        }
    }

    public function testNumberOfCourses()
    {
        $client = $this->getClient();
        $crawler = $client->request('GET', "/courses");
        $countOnDB = count(static::getContainer()->get(CourseRepository::class)->findAll());
        $this->assertCount($countOnDB, $crawler->filter('.course-card'));
    }

    public function testNotExistedCourseSuccessfully()
    {
        $this->getClient()->request('GET', "/courses/-413241");
        $this->assertResponseNotFound();
    }

    public function testCourseAdding()
    {
        $client = $this->getClient();
        $countBefore = count(static::getContainer()->get(CourseRepository::class)->findAll());
        $crawler = $this->getClient()->request('GET', '/courses');
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
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
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
    }

    public function testAddCourseWithEmptyName()
    {
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
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
    }

    public function testAddCourseWithNotUniqueCharacterCode()
    {
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
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
    }

    public function testAddCourseWithCharacterCodeLengthGreaterThanConstraint()
    {
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
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
    }

    public function testAddCourseWithNameLengthGreaterThanConstraint()
    {
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
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
    }

    public function testAddCourseWithDescriptionLengthGreaterThanConstraint()
    {
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
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
    }

    public function testEditCourse()
    {
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
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
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
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
