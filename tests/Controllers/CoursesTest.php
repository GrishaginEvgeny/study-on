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
use Symfony\Component\DomCrawler\Crawler;

use function DeepCopy\deep_copy;

class CoursesTest extends AbstractTest
{
    protected function getFixtures(): array
    {
        return [AppFixtures::class];
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
        $this->adminLogin($client);
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
        $this->adminLogin($client);
        $countBefore = count(static::getContainer()->get(CourseRepository::class)->findAll());
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();
        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[characterCode]' => 'test',
                'course[name]' => 'test',
                'course[description]' => 'test',
                'course[cost]' => 0,
                'course[type]' => Course::FREE_TYPE
            ]
        );
        $client->submit($form);

        $addedCourse = static::getContainer()->get(CourseRepository::class)->findOneBy(['characterCode' => 'test']);
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
        $this->adminLogin($client);
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();

        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[characterCode]' => '',
                'course[name]' => 'test',
                'course[description]' => 'test',
                'course[cost]' => 0,
                'course[type]' => Course::FREE_TYPE
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
        $this->assertSelectorTextContains(
            '.invalid-feedback.d-block',
            'Поле "Cимвольный код" не должно быть пустым.'
        );
    }

    public function testAddCourseWithWrongCharacterCode()
    {
        $client = $this->billingClient();
        $this->adminLogin($client);
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();

        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[characterCode]' => '^&#$##@fdad',
                'course[name]' => 'test',
                'course[description]' => 'test',
                'course[cost]' => 0,
                'course[type]' => Course::FREE_TYPE
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
        $this->assertSelectorTextContains(
            '.invalid-feedback.d-block',
            'В поле "Cимвольный код" могут содержаться только цифры и латиница.'
        );
    }

    public function testAddCourseWithEmptyName()
    {
        $client = $this->billingClient();
        $this->adminLogin($client);
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();

        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[characterCode]' => 'php',
                'course[name]' => '',
                'course[description]' => 'test',
                'course[cost]' => 0,
                'course[type]' => Course::FREE_TYPE
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
        $this->assertSelectorTextContains(
            '.invalid-feedback.d-block',
            'Поле "Название" не должно быть пустым.'
        );
    }

    public function testAddCourseWithNotUniqueCharacterCode()
    {
        $client = $this->billingClient();
        $this->adminLogin($client);
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();

        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[characterCode]' => static::getContainer()
                    ->get(CourseRepository::class)
                    ->findAll()[0]
                    ->getCharacterCode(),
                'course[name]' => 'test',
                'course[description]' => 'test',
                'course[cost]' => 0,
                'course[type]' => Course::FREE_TYPE
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
        $this->assertSelectorTextContains(
            '.invalid-feedback.d-block',
            'Курс с таким символьным кодом уже существует.'
        );
    }

    public function testAddCourseWithCharacterCodeLengthGreaterThanConstraint()
    {
        $client = $this->billingClient();
        $this->adminLogin($client);
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();

        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[characterCode]' => str_repeat("a", 256),
                'course[name]' => 'test',
                'course[description]' => 'test',
                'course[cost]' => 0,
                'course[type]' => Course::FREE_TYPE
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
        $this->assertSelectorTextContains(
            '.invalid-feedback.d-block',
            'Поле "Cимвольный код" не должно быть длинной более 255 символов.'
        );
    }

    public function testAddCourseWithNameLengthGreaterThanConstraint()
    {
        $client = $this->billingClient();
        $this->adminLogin($client);
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();

        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[characterCode]' => 'test',
                'course[name]' => str_repeat("a", 256),
                'course[description]' => 'test',
                'course[cost]' => 0,
                'course[type]' => Course::FREE_TYPE
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
        $this->assertSelectorTextContains(
            '.invalid-feedback.d-block',
            'Поле "Название" не должно быть длинной более 255 символов.'
        );
    }

    public function testAddCourseWithDescriptionLengthGreaterThanConstraint()
    {
        $client = $this->billingClient();
        $this->adminLogin($client);
        $crawler = $client->request('GET', '/courses');
        $this->assertResponseOk();

        $addLink = $crawler->filter('.add_new_course')->link();
        $crawler = $client->click($addLink);
        $this->assertResponseOk();

        $form = $crawler->selectButton('Сохранить')->form(
            [
                'course[characterCode]' => 'test',
                'course[name]' => 'test',
                'course[description]' => str_repeat("a", 1001),
                'course[cost]' => 0,
                'course[type]' => Course::FREE_TYPE
            ]
        );
        $client->submit($form);
        $this->assertSelectorExists('.invalid-feedback.d-block');
        $this->assertSelectorTextContains(
            '.invalid-feedback.d-block',
            'Поле "Описание" не должно быть длинной более 1000 символов.'
        );
    }

    public function testEditCourse()
    {
        $client = $this->billingClient();
        $this->adminLogin($client);
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
                'course[characterCode]' => 'test123',
                'course[name]' => 'testName',
                'course[description]' => 'test',
                'course[cost]' => 0,
                'course[type]' => Course::FREE_TYPE
            ]
        );
        $client->submit($form);

        $editedCourse = static::getContainer()->get(CourseRepository::class)->findOneBy(['characterCode' => 'test123']);
        $this->assertNotNull($editedCourse);
    }

    public function testRemoveCourse()
    {
        $client = $this->billingClient();
        $this->adminLogin($client);
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

    public function testTransactionSuccessfully()
    {
        //без фильтров
        $client = $this->billingClient();
        $this->adminLogin($client);
        $crawler = $client->request('GET', '/courses');
        $linkProfile = $crawler->filter('.person-link')->link();
        $crawler = $client->click($linkProfile);
        $this->assertResponseOk();
        $transactionLink = $crawler->filter('.transaction-link')->link();
        $crawler = $client->click($transactionLink);
        $this->assertResponseOk();
        $allTransactions = [
            [
                0 => (new \DateTimeImmutable('-3 week'))->format('Y-m-d H:i:s'),
                1 => "Депозит",
                2 => '',
                3 => '111111111111$',
                4 => ''
            ],
            [
                0 => (new \DateTimeImmutable('-2 week'))->format('Y-m-d H:i:s'),
                1 => "Платёж",
                2 => 'pydev',
                3 => '99.99$',
                4 => (new \DateTimeImmutable('-1 week'))->format('Y-m-d H:i:s')
            ],
            [
                0 => (new \DateTimeImmutable('-1 day'))->format('Y-m-d H:i:s'),
                1 => "Платёж",
                2 => 'webdev',
                3 => '199.99$',
                4 => ''
            ]
        ];
        foreach ($allTransactions as $key => $transaction) {
            $selector = ".trans-row-{$key}";
            $row = $crawler->filter($selector);
            $row->each(function (Crawler $node, $i) use ($transaction) {
                $this->assertSame($node->text(), $transaction[$i]);
            });
        }

        //фильтр на тип транзакции -- deposit
        $form = $crawler->selectButton('Применить')->form();
        $formValues = $form->getPhpValues();
        $formValues['form']['type'] = ["deposit"];
        $crawler = $client->request($form->getMethod(), $form->getUri(), $formValues);
        $this->assertResponseOk();
        $depositTransaction = $allTransactions;
        unset($depositTransaction[1], $depositTransaction[2]);
        foreach ($depositTransaction as $key => $transaction) {
            $selector = ".trans-row-{$key}";
            $row = $crawler->filter($selector);
            $row->each(function (Crawler $node, $i) use ($transaction) {
                $this->assertSame($node->text(), $transaction[$i]);
            });
        }

        //фильтр на тип транзакции -- payment
        $form = $crawler->selectButton('Применить')->form();
        $formValues = $form->getPhpValues();
        $formValues['form']['type'] = ["payment"];
        $crawler = $client->request($form->getMethod(), $form->getUri(), $formValues);
        $this->assertResponseOk();
        $paymentTransaction = $allTransactions;
        unset($paymentTransaction[0]);
        foreach ($paymentTransaction as $key => $transaction) {
            $selector = ".trans-row-{$key}";
            $row = $crawler->filter($selector);
            $row->each(function (Crawler $node, $i) use ($transaction) {
                $this->assertSame($node->text(), $transaction[$i]);
            });
        }

        //фильтр на код курса
        $form = $crawler->selectButton('Применить')->form();
        $formValues = $form->getPhpValues();
        $formValues['form']['course_code'] = "webdev";
        $crawler = $client->request($form->getMethod(), $form->getUri(), $formValues);
        $this->assertResponseOk();
        $codeTransaction = $allTransactions;
        unset($codeTransaction[0], $codeTransaction[1]);
        foreach ($codeTransaction as $key => $transaction) {
            $selector = ".trans-row-{$key}";
            $row = $crawler->filter($selector);
            $row->each(function (Crawler $node, $i) use ($transaction) {
                $this->assertSame($node->text(), $transaction[$i]);
            });
        }

        //фильтр на флаг
        $form = $crawler->selectButton('Применить')->form();
        $formValues = $form->getPhpValues();
        $formValues['form']['skip_expired'] = true;
        $crawler = $client->request($form->getMethod(), $form->getUri(), $formValues);
        $this->assertResponseOk();
        $flagTransaction = $allTransactions;
        unset($flagTransaction[1]);
        foreach ($flagTransaction as $key => $transaction) {
            $selector = ".trans-row-{$key}";
            $row = $crawler->filter($selector);
            $row->each(function (Crawler $node, $i) use ($transaction) {
                $this->assertSame($node->text(), $transaction[$i]);
            });
        }
    }

    public function testTransactionUnsuccessfully()
    {
        $client = $this->billingClient();
        $this->adminLogin($client);
        $crawler = $client->request('GET', '/courses');
        $linkProfile = $crawler->filter('.person-link')->link();
        $crawler = $client->click($linkProfile);
        $this->assertResponseOk();
        $transactionLink = $crawler->filter('.transaction-link')->link();
        $crawler = $client->click($transactionLink);
        $this->assertResponseOk();
        $form = $crawler->selectButton('Применить')->form();
        $formValues = $form->getPhpValues();
        $formValues['transaction_form']['type'] = [];
        $client->request($form->getMethod(), $form->getUri(), $formValues);
        $this->assertSelectorExists('.invalid-feedback.d-block');
        $this->assertSelectorTextContains(
            '.invalid-feedback.d-block',
            'Вы должны выбрать хотя бы один тип.'
        );
    }

    public function testBuyCourse()
    {
        $client = $this->billingClient();
        $this->adminLogin($client);
        $crawler = $client->request('GET', '/courses');
        $crawler->filter('.link_to_course')->each(function (Crawler $node, $i) use ($client) {
            if (
                $node->previousAll()->last()->text() === "Обучение шахматам"
                    || $node->previousAll()->last()->text() === "Разработчик десктопных приложений"
                    || $node->previousAll()->last()->text() === "Python-разработчик"
            ) {
                $link = $node->link();
                $crawler = $client->click($link);
                $this->assertResponseOk();
                $this->assertSelectorExists('#buyModal');
                $buyLink = $crawler->filter('.buy-link')->link();
                $client->click($buyLink);
                $this->assertResponseRedirect();
                $crawler = $client->followRedirect();
                $this->assertSelectorExists('.notification-message');
                        $this->assertSelectorTextContains(
                            '.notification-message',
                            'Курс успешно оплачен.'
                        );
            } else {
                $link = $node->link();
                $crawler = $client->click($link);
                $this->assertResponseOk();
                $this->assertSelectorNotExists('#buyModal');
            }
        });
    }
}
