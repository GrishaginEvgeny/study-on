<?php

namespace App\Tests\Controllers;

use App\DataFixtures\UserFixtures;
use App\Repository\CourseRepository;
use App\Repository\LessonRepository;
use App\Tests\AbstractTest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class LessonsTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [UserFixtures::class];
    }

    public function testPagesOnGETRequests()
    {
        $client = $this->getClient();
        $lessons = static::getContainer()->get(LessonRepository::class)->findAll();
        $courses = static::getContainer()->get(CourseRepository::class)->findAll();
        foreach ($lessons as $lesson) {
            $client->request('GET', "/lessons/{$lesson->getId()}/edit");
            $this->assertResponseOk();

            $client->request('GET', "/lessons/{$lesson->getId()}");
            $this->assertResponseOk();
        }

        foreach ($courses as $course) {
            $client->request('GET', "/lessons/new?id={$course->getId()}");
            $this->assertResponseOk();
        }
        $this->assertResponseOk();
    }

    public function testPagesOnPOSTRequests()
    {
        $client = $this->getClient();
        $lessons = static::getContainer()->get(LessonRepository::class)->findAll();
        $courses = static::getContainer()->get(CourseRepository::class)->findAll();
        foreach ($lessons as $lesson) {
            $client->request('GET', "/lessons/{$lesson->getId()}/edit");
            $this->assertResponseOk();

            $client->request('POST', "/lessons/{$lesson->getId()}");
            $this->assertResponseRedirect();
        }
        foreach ($courses as $course) {
            $client->request('POST', "/lessons/new?id={$course->getId()}");
            $this->assertResponseOk();
        }
        $this->assertResponseOk();
    }

    public function testNumberOfLessonByCourse()
    {
        $client = $this->getClient();
        $courses = static::getContainer()->get(CourseRepository::class)->findAll();
        foreach ($courses as $course) {
            $crawler = $client->request('GET', "/courses/{$course->getId()}");
            $countOnDB = count($course->getLessons());
            $this->assertCount($countOnDB, $crawler->filter('.lesson_field'));
        }
    }

    public function testNotExistedLesson()
    {
        $this->getClient()->request('GET', "/lessons/-413241");
        $this->assertResponseNotFound();
    }


    public function testAddLessonSuccessfully()
    {
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
        $this->assertResponseOk();


        $courseLink = $crawler->filter('.link_to_course')->link();
        $crawler = $client->click($courseLink);
        $this->assertResponseOk();

        $addLessonLink = $crawler->filter('.link_to_add_lesson')->link();
        $crawler = $client->click($addLessonLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'lesson[Name]' => 'test',
                'lesson[SequenceNumber]' => '111',
                'lesson[Content]' => 'test',
            ]
        );
        $lessonsCourseId = $form['lesson[Course]']->getValue();
        $lessonsCourseBefore = static::getContainer()
            ->get(CourseRepository::class)
            ->findOneBy(['id' => $lessonsCourseId ]);
        $lessonsCountByCourseBefore = count($lessonsCourseBefore->getLessons());
        $client->submit($form);
        $lessonsCourseAfter = static::getContainer()
            ->get(CourseRepository::class)
            ->findOneBy(['id' => $lessonsCourseId ]);
        $lessonsCountByCourseAfter = count($lessonsCourseAfter->getLessons());
        $this->assertEquals($lessonsCountByCourseAfter, $lessonsCountByCourseBefore + 1);
        $crawler = $client->followRedirect();
        $this->assertSame($lessonsCourseAfter->getName(), $crawler->filter('.course_page_header')->text());
        $this->assertCount($lessonsCountByCourseAfter, $crawler->filter('.lesson_field'));
    }


    public function testAddLessonWithEmptyName()
    {
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
        $this->assertResponseOk();


        $courseLink = $crawler->filter('.link_to_course')->link();
        $crawler = $client->click($courseLink);
        $this->assertResponseOk();

        $addLessonLink = $crawler->filter('.link_to_add_lesson')->link();
        $crawler = $client->click($addLessonLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'lesson[Name]' => '',
                'lesson[SequenceNumber]' => '111',
                'lesson[Content]' => 'test',
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
    }

    public function testAddLessonWithWrongSequenceNumber()
    {
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
        $this->assertResponseOk();


        $courseLink = $crawler->filter('.link_to_course')->link();
        $crawler = $client->click($courseLink);
        $this->assertResponseOk();

        $addLessonLink = $crawler->filter('.link_to_add_lesson')->link();
        $crawler = $client->click($addLessonLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'lesson[Name]' => 'test',
                'lesson[SequenceNumber]' => '',
                'lesson[Content]' => 'test',
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
    }

    public function testAddLessonWithEmptyContent()
    {
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
        $this->assertResponseOk();


        $courseLink = $crawler->filter('.link_to_course')->link();
        $crawler = $client->click($courseLink);
        $this->assertResponseOk();

        $addLessonLink = $crawler->filter('.link_to_add_lesson')->link();
        $crawler = $client->click($addLessonLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'lesson[Name]' => 'test',
                'lesson[SequenceNumber]' => '111',
                'lesson[Content]' => '',
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
    }

    public function testAddLessonWithNameGreaterThanConstraint()
    {
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
        $this->assertResponseOk();


        $courseLink = $crawler->filter('.link_to_course')->link();
        $crawler = $client->click($courseLink);
        $this->assertResponseOk();

        $addLessonLink = $crawler->filter('.link_to_add_lesson')->link();
        $crawler = $client->click($addLessonLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'lesson[Name]' => str_repeat("a", 256),
                'lesson[SequenceNumber]' => '111',
                'lesson[Content]' => 'test',
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
    }

    public function testAddLessonWithSequenceNumberGreaterThanConstraint()
    {
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
        $this->assertResponseOk();


        $courseLink = $crawler->filter('.link_to_course')->link();
        $crawler = $client->click($courseLink);
        $this->assertResponseOk();

        $addLessonLink = $crawler->filter('.link_to_add_lesson')->link();
        $crawler = $client->click($addLessonLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'lesson[Name]' => 'test',
                'lesson[SequenceNumber]' => '10001',
                'lesson[Content]' => 'test',
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
    }

    public function testEditLesson()
    {
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
        $this->assertResponseOk();


        $courseLink = $crawler->filter('.link_to_course')->link();
        $crawler = $client->click($courseLink);
        $this->assertResponseOk();

        $editLessonLink = $crawler->filter('.link_to_edit_lesson')->link();
        $crawler = $client->click($editLessonLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Обновить информацию об уроке')->form(
            [
                'lesson[Name]' => 'test',
                'lesson[SequenceNumber]' => '999',
                'lesson[Content]' => 'test',
            ]
        );
        $lessonsCourseId = $form['lesson[Course]']->getValue();
        $client->submit($form);
        $course = static::getContainer()
            ->get(CourseRepository::class)
            ->findOneBy(['id' => $lessonsCourseId ]);
        $lesson = static::getContainer()
            ->get(LessonRepository::class)
            ->findOneBy(['Course' => $course, 'SequenceNumber' => 999]);
        $this->assertNotNull($lesson);
        $crawler = $client->followRedirect();
        $this->assertResponseOk();
        $this->assertSame($course->getName(), $crawler->filter('.course_page_header')->text());
    }

    public function testRemoveLesson()
    {
        $client = $this->getClient();
        $crawler = $this->getClient()->request('GET', '/courses');
        $this->assertResponseOk();

        $courseLink = $crawler->filter('.link_to_course')->link();
        $crawler = $client->click($courseLink);
        $this->assertResponseOk();

        $nameOfCourse = $crawler->filter('.course_page_header')->text();
        $descriptionOfCourse = $crawler->filter('.course_description')->text();
        $course = static::getContainer()
            ->get(CourseRepository::class)
            ->findOneBy(['Name' => $nameOfCourse, 'Description' => $descriptionOfCourse ]);
        $courseLessonCountBefore = count($course->getLessons());

        $lessonLink = $crawler->filter('.link_to_lesson')->link();
        $crawler = $client->click($lessonLink);
        $this->assertResponseOk();

        $client->submitForm('Удалить урок');


        $crawler = $client->followRedirect();
        $this->assertResponseOk();

        $nameOfCourse = $crawler->filter('.course_page_header')->text();
        $descriptionOfCourse = $crawler->filter('.course_description')->text();
        $course = static::getContainer()
            ->get(CourseRepository::class)
            ->findOneBy(['Name' => $nameOfCourse, 'Description' => $descriptionOfCourse ]);
        $courseLessonCountAfter = count($course->getLessons());
        $this->assertEquals($courseLessonCountBefore - 1, $courseLessonCountAfter);
    }
}
