<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Course;
use App\Entity\Lesson;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $courses = [
            'pythonDeveloper' => new Course(),
            'layoutDesigner' => new Course(),
            'webDeveloper' => new Course(),
            'chessPlayer' => new Course(),
            'desktopDeveloper' => new Course(),
        ];

        $courses['pythonDeveloper']
            ->setCharacterCode('pydev')
            ->setName('Python-разработчик')
            ->setDescription('Станьте инженером-программистом на одном из 
        самых простых и популярных языков программирования Python. 
        На практике научитесь понимать фундаментальные алгоритмы и использовать 
        их для решения задач. Вы сможете писать сайты, приложения, 
        нейросети и программы для научных исследований, 
        Telegram-бота. 
        Вы сможете трудоустроиться после 9 месяцев обучения.');

        $courses['layoutDesigner']
            ->setCharacterCode('layoutdesigner')
            ->setName('Верстальщик')
            ->setDescription('Станьте верстальщиком и вы 
        сможете создавать сайты по макетам дизайнера с нуля и в 
        CMS-системах. Вы научитесь с помощью CSS и языка HTML 
        описывать расположение блоков, картинок, текста и видео, 
        а так же анимировать их. А изучив язык JavaScript, 
        добавите к ним бизнес-логику, динамику и 
        настроить работу с данными по api.');

        $courses['webDeveloper']
            ->setCharacterCode('webdev')
            ->setName('Веб-разработчик')
            ->setDescription('Станьте веб-разработчиком и получите одну 
        из самых востребованных профессий в IT. 
        Вы изучите основы программирования, принципы 
        работы баз данных и основные алгоритмы, а также 
        овладеете необходимыми технологиями и программами. 
        На практике освоите языки JavaScript и TypeScript, 
        научитесь создавать адаптивные сайты и интерактивные веб-приложения. 
        Через 9 месяцев вы сможете трудоустроиться.');

        $courses['chessPlayer']
            ->setCharacterCode('chessPlayer')
            ->setName('Обучение шахматам')
            ->setDescription('Каждый урок Шахматы Уроки Обучение содержит теоретический и практический материал, 
            а также домашнее задание. После изучения каждого урока шахматы для начинающих и новичков 
            шахматист должен сыграть 
            1-2 партии с соперником или с шахматной программой на соответствующем уровне сложности. 
            Обучение шахматам онлайн 
            будет эффективным, если вы занимаетесь по нашим шахматы видео урокам.');

        $courses['desktopDeveloper']
            ->setCharacterCode('desktopDeveloper')
            ->setName('Разработчик десктопных приложений')
            ->setDescription('Этот курс предназначен для студентов, 
            которые увлечены программированием и хотят разработать свой собственный 
            продукт и внести свой вклад в индустрию программного обеспечения. 
            Этот курс поможет вам получить передовые знания о том, 
            как работает традиционное программное обеспечение и какая наука стоит за ним.
            После прохождения этого курса вы узнаете :

            -Как создать индивидуальное приложение, основанное на требованиях пользователя

            -Как используются элементы управления

            -Что такое обработка событий

            -Как управлять данными с помощью базы данных

            -Различное поведение элементов управления

            -Операции с базой данных на основе LINQ

            -Навигация

            -Окна Master/MDI

            -Как создать исполняемый файл для вашего программного обеспечения (развертывание приложения).

            -Что такое M.A.R.S');


        $pythonLessons = [];
        $layoutDesignerLessons = [];
        $webDeveloperLessons = [];
        $chessLessons = [];
        $desktopLessons = [];

        for ($i = 0; $i < 4; $i++) {
            $pythonLessons[$i] = new Lesson();
            $pythonLessons[$i]->setSequenceNumber($i + 1);
            $chessLessons[$i] = new Lesson();
            $chessLessons[$i]->setSequenceNumber($i + 1);
            $desktopLessons[$i] = new Lesson();
            $desktopLessons[$i]->setSequenceNumber($i + 1);
            $layoutDesignerLessons[$i] = new Lesson();
            $layoutDesignerLessons[$i]->setSequenceNumber($i + 1);
            $webDeveloperLessons[$i] = new Lesson();
            $webDeveloperLessons[$i]->setSequenceNumber($i + 1);
        }

        $pythonLessons[0]
            ->setName('Основы синтаксиса')
            ->setContent('Синтаксис языка Python, как и сам язык, очень прост.

        Синтаксис
        Конец строки является концом инструкции (точка с запятой не требуется).
        
        Вложенные инструкции объединяются в блоки по величине отступов. 
        Отступ может быть любым, главное, 
        чтобы в пределах одного вложенного блока отступ был одинаков.
         И про читаемость кода не забывайте. 
         Отступ в 1 пробел, к примеру, не лучшее решение. 
         Используйте 4 пробела (или знак табуляции, на худой конец).
        
        Вложенные инструкции в Python записываются 
        в соответствии с одним и тем же шаблоном, 
        когда основная инструкция завершается двоеточием, 
        вслед за которым располагается вложенный блок кода, 
        обычно с отступом под строкой основной инструкции.');


        $pythonLessons[1]
            ->setName('Переменные и типы данных')
            ->setContent('Переменные предназначены для хранения данных. 
        Название переменной в Python должно начинаться с алфавитного символа 
        или со знака подчеркивания и может 
        содержать алфавитно-цифровые символы и знак подчеркивания. 
        И кроме того, название переменной не должно 
        совпадать с названием ключевых слов языка Python.
        
        Переменная хранит данные одного из типов данных. 
        В Python существует множество различных типов данных. 
        В данном случае рассмотрим 
        только самые базовые типы: bool, int, float, complex и str.
        
        Python является языком с динамической типизацией. 
        А это значит, что переменная не привязана жестко с определенному типу.

        Тип переменной определяется исходя из значения, 
        которое ей присвоено. 
        Так, при присвоении строки в двойных 
        или одинарных кавычках переменная имеет тип str. 
        При присвоении целого числа 
        Python автоматически определяет тип переменной как int. 
        Чтобы определить переменную как объект float, 
        ей присваивается дробное число, 
        в котором разделителем целой и дробной части является точка.');

        $pythonLessons[2]
            ->setName('Условные конструкции')
            ->setContent('Условные конструкции используют условные выражения
         и в зависимости от их значения направляют выполнение программы
         по одному из путей. Одна из таких конструкций - это конструкция if.
        
         В самом простом виде после ключевого слова if идет логическое выражение.
         И если это логическое выражение возвращает True, 
         то выполняется последующий блок инструкций, 
         каждая из которых должна начинаться с новой 
         строки и должна иметь отступы от начала выражения if.');

        $pythonLessons[3]
            ->setName('Циклы')
            ->setContent('Циклы позволяют выполнять некоторое действие
         в зависимости от соблюдения некоторого условия. 
         В языке Python есть следующие типы циклов: for и while.

         Цикл while проверяет истинность некоторого условия, 
         и если условие истинно, то выполняет инструкции цикла.
         Цикл for пробегается по набору значений,
         помещает каждое значение в переменную, 
         и затем в цикле мы можем с этой переменной 
         производить различные действия.');

        $layoutDesignerLessons[0]
            ->setName('Figma')
            ->setContent('Figma — онлайн-сервис для разработки интерфейсов 
        и прототипирования с возможностью 
        организации совместной работы в режиме реального времени.

        Сервис доступен по подписке, 
        предусмотрен бесплатный тарифный план для одного пользователя. 
        Имеются офлайн-версии для Windows, macOS. 
        Реализована интеграция с корпоративным мессенджером Slack 
        и инструментом прототипирования Framer. 
        Используется как для создания упрощённых прототипов интерфейсов,
        так и для детальной проработки дизайна
        интерфейсов мобильных приложений, веб-сайтов, 
        корпоративных порталов.');

        $layoutDesignerLessons[1]
            ->setName('HTML')
            ->setContent('HTML 
        (от англ. HyperText Markup Language — «язык гипертекстовой разметки») — 
        стандартизированный язык 
        гипертекстовой разметки документов для просмотра веб-страниц в браузере. 
        Веб-браузеры получают HTML документ от сервера по 
        протоколам HTTP/HTTPS или открывают с локального диска, 
        далее интерпретируют код в интерфейс, 
        который будет отображаться на экране монитора.

        Элементы HTML являются строительными блоками HTML страниц. 
        С помощью HTML разные конструкции, изображения и другие объекты,
        такие как интерактивная веб-форма,
        могут быть встроены в отображаемую страницу. 
        HTML предоставляет средства для создания 
        заголовков, абзацев, списков, ссылок,
        цитат и других элементов. Элементы HTML выделяются тегами, 
        записанными с использованием угловых скобок.
        Такие теги, как img  и input ,
        напрямую вводят контент на страницу. 
        Другие теги, такие как p, окружают и оформляют текст внутри себя
        и могут включать другие теги в качестве подэлементов. 
        Браузеры не отображают HTML-теги, 
        но используют их для интерпретации содержимого страницы.
        
        Язык XHTML является более строгим вариантом HTML, 
        он следует синтаксису XML 
        и является приложением языка XML 
        в области разметки гипертекста');

        $layoutDesignerLessons[2]
            ->setName('CSS')
            ->setContent('CSS (англ. Cascading Style Sheets «каскадные таблицы стилей»)
         — формальный язык описания внешнего вида документа (веб-страницы), 
         написанного с использованием языка разметки (чаще всего HTML или XHTML). 
         Также может применяться к любым XML-документам, например, к SVG или XUL.');

        $layoutDesignerLessons[3]
            ->setName('Bootstrap')
            ->setContent('Bootstrap — свободный набор инструментов для 
        создания сайтов и веб-приложений. 
        Включает в себя HTML- и CSS-шаблоны оформления для типографики, 
        веб-форм, кнопок, меток, 
        блоков навигации и прочих компонентов веб-интерфейса, 
        включая JavaScript-расширения.');


        $webDeveloperLessons[0]
            ->setName('Введение')
            ->setContent('Веб-разработка — процесс создания веб-сайта или веб-приложения. 
        Основными этапами процесса являются веб-дизайн, вёрстка страниц,
         программирование на стороне клиента и сервера, а также конфигурирование веб-сервера.
        
        На сегодняшний день существуют несколько этапов разработки веб-сайта:

        - Проектирование сайта или веб-приложения (сбор и анализ требований, разработка технического задания, 
        проектирование интерфейсов);
        - Разработка креативной концепции сайта;
        - Создание дизайн-концепции сайта;
        - Создание макетов страниц;
        - Создание мультимедиа-объектов;
        - Вёрстка страниц и шаблонов;
        - Программирование (разработка функциональных инструментов) 
        или интеграция в систему управления содержимым (CMS);
        - Оптимизация и размещение[уточнить] материалов сайта;
        - Тестирование и внесение корректировок;
        - Публикация проекта на хостинге;
        - Обслуживание работающего сайта или его программной основы.
        В зависимости от текущей задачи, какие-то из этапов могут отсутствовать.
        ');

        $webDeveloperLessons[1]
            ->setName('Front-end')
            ->setContent('Фронтенд (англ. frontend) — презентационная часть информационной или программной системы, 
        её пользовательский интерфейс и связанные с ним компоненты; 
        применяется в соотношении с базисной частью системы, 
        её внутренней реализацией, называемой в этом случае бэкендом (англ. backend).
        
        Разделение программных систем на фронтенд и бэкенд — 
        одно из стандартных решений для архитектуры программного обеспечения, 
        связанное в программной инженерии с принципом разделения ответственности 
        между внешним представлением и внутренней реализацией. 
        Как правило, бэкенд реализует API, используемые фронтендом, 
        и таким образом фронтенд-разработчику не нужно знать особенности реализации внутренней части,
        а бэкенд-разработчику — интерфейсные решения. 
        Кроме того, такое разделение позволяет использовать различные инструменты 
        для реализации внутренней и внешней части системы, более эффективные для соответствующих задач. 
        Например, в веб-разработке к технологиям фронтенда относятся HTML, CSS, JavaScript;
        ');

        $webDeveloperLessons[2]
            ->setName('Back-end')
            ->setContent('Бэкенд (англ. backend) —  Внутренняя составляющая сайта, его “начинка”, 
        административная зона. 
        То есть та часть сайта, 
        которая работает на сервере: 
        получает запросы от пользователей из фротэнда и обрабатывает их. 
        С Back-end работают администраторы сайта и программисты.
        
        Для бэкенда вы можете использовать любые инструменты, 
        доступные на вашем сервере 
        (который, по сути, является просто компьютером, настроенным для ответов на сообщения). 
        Это означает, что вы можете использовать любой универсальный язык программирования: 
        Ruby, PHP, Python, Java, JavaScript / Node, bash. 
        Это также означает, что вы можете использовать системы управления базами данных, 
        такие как MySQL, PostgreSQL, MongoDB, Cassandra, Redis, Memcached.
        ');

        $webDeveloperLessons[3]
            ->setName('GIT')
            ->setContent('Git (произносится «гит»[7]) — распределённая система управления версиями.
        
        Система управления версиями (также используется определение «система контроля версий[1]», 
        (от англ. Version Control System, VCS или Revision Control System) — 
        программное обеспечение для облегчения работы с изменяющейся информацией. 
        Система управления версиями позволяет хранить несколько версий одного и того же документа, 
        при необходимости возвращаться к более ранним версиям, определять, 
        кто и когда сделал то или иное изменение, и многое другое.
        Такие системы наиболее широко используются при разработке программного обеспечения для хранения исходных 
        кодов разрабатываемой программы. 
        Однако они могут с успехом применяться и в других областях, 
        в которых ведётся работа с большим количеством непрерывно изменяющихся электронных документов. 
        В частности, системы управления версиями применяются в САПР, 
        обычно в составе систем управления данными об изделии (PDM). 
        Управление версиями используется в инструментах конфигурационного управления 
        (Software Configuration Management Tools).');

        $desktopLessons[0]
            ->setName('Основная терминология')
            ->setContent('Кодовый файл — это один из файлов на языке C#.
            Проект — это совокупность кодовых файлов, 
            которые могут быть скомпилированы в сборку: программу или библиотеку.
            Сборка — это, соответственно, результат компиляции проекта. 
            Как правило это *.exe или *.dll файл, содержащий инструкции для компьютера.
            Решение (solution) — это несколько проектов, 
            объединенные общими библиотеками и задачами. 
            Как правило открывать с помощью Visual Studio 
            нужно именно файл решения (.sln), хотя можно открыть и отдельный проект (.csproj файл). 
            Имейте в виду, если открыть отдельный кодовый файл, не открывая проект или решение, 
            то не будет возможности его запустить. Это распространённая ошибка новичков.
            Reference — ссылка внутри проекта на другие сборки. 
            Только сославшись на другую сборку можно будет использовать код из неё.
            Метод — это последовательность действий. Аналог функций, 
            процедур и подпрограмм в других языках. 
            В устной речи часто используют все эти слова как синонимы, 
            но в спецификации на язык C# используется термин «метод».
            Класс — это совокупность данных и методов. 
            Все сборки состоят из скомпилированных классов.
            Пространство имен — это совокупность классов, 
            логически связанных между собой.
            Между сборками и пространствами имен нет прямого соответствия: 
            в сборке может хранится несколько пространств имен, 
            а разные классы одного пространства имен могут быть определены в разных сборках.
            После успешной компиляции, в директории проекта создается 
            поддиректория bin/Debug, 
            в которой и оказывается сборка — результат компиляции — exe или dll файлы вашей программы.');

        $desktopLessons[1]
            ->setName('Переменные и константы')
            ->setContent('Переменная представляет числовое или строковое значение или объект класса. 
            Значение, хранящееся в переменной, может измениться, однако имя остается прежним. 
            Переменная представляет собой один тип поля. Следующий код является простым примером 
            объявления целочисленной переменной, 
            присвоения ей значения и последующего присвоения нового значения.
            
            В C# переменные объявляются с определенным типом данных и надписью. 
            Если ранее вам приходилось работать со слабо типизированными языками, 
            например JScript, вы привыкли использовать один тип "var" для всех переменных, 
            то в C# необходимо указать тип переменной: int, float, byte, short или другой из 
            более чем 20 различных типов данных. Тип указывает, помимо всего прочего, 
            точный объем памяти, который следует выделить для хранения значения 
            при выполнении приложения. При преобразовании переменной из одного 
            типа в другой язык C# следует определенным правилам.
            
            Константа является другим типом поля. 
            Она хранит значение, присваиваемое по завершении компиляции программы, 
            и никогда после этого не изменяется. 
            Константы объявляются помощью ключевого слова const; 
            их использование способствует повышению удобочитаемости кода.');


        $desktopLessons[2]
            ->setName('Операторы')
            ->setContent('Язык C# предоставляет большой набор операторов, 
            которые представляют собой символы, определяющие операции, 
            которые необходимо выполнить с выражением. 
            Обычно в перечислениях допускается использование операций 
            с целочисленными типами, например ==, !=, <, >, <=, >=, binary +, binary -, ^, &, |, ~, ++, -- и sizeof(). 
            Кроме того, многие операторы могут перегружаться пользователем. 
            Таким образом, их значение при применении к пользовательскому типу меняется.');


        $desktopLessons[3]
            ->setName('Циклы')
            ->setContent('Циклом называется один или несколько операторов, 
            повторяющихся заданное число раз или до тех пор, 
            пока не будет выполнено определенное условие. 
            Выбор типа цикла зависит от задачи программирования и 
            личных предпочтений кодирования. Одним из основных отличий C# 
            от других языков, таких как C++, 
            является цикл foreach, 
            разработанный для упрощения итерации по массиву или коллекции.');

        $chessLessons[0]
            ->setName('Тактика: Двойной Удар')
            ->setContent('Исключительно важным в практической игре типом комбинации служит двойной удар 
            (двойное нападение).
            Под двойным ударом понимают одновременное нападение фигуры или пешки на два объекта противника. 
            Очень редко в шахматных партиях случается тройной удар. 
            Двойной удар это один из базовых тактических приемов в шахматном сражении.
            Он случается на всех этапах шахматной партии и его способны совершить все фигуры, 
            даже король. Наиболее продуктивные и виртуозные исполнители данной тактической операции – ферзь и конь. 
            Это и не удивительно, так как их ударность проявляется в восьми направлениях. 
            Ферзь к тому же самая сильная фигура (держит под обстрелом почти пол доски), 
            а конь обладает уникальной способностью перепрыгивать 
            через свои и неприятельские фигуры. 
            Двойной удар коня и пешки в шахматной литературе получил название «вилки».');

        $chessLessons[1]
            ->setName('Шахматные фигуры')
            ->setContent('Король по сути ключевая фигура. Если королю поставили мат(сделали шах, 
            а все другие поля отобраны вражескими фигурами), 
            то партия проиграна. Король может пойти на любое соседнее с ним поле.
            
            Ферзь это вторая по значимости фигура после короля. 
            Ферзь может ходить как по вертикалям и горизонталям 
            так и по диагоналям. 
            За один ход ферзь может перенестись через всю доску от края до края. 
            
            Ладью называют тяжелой фигурой. 
            Движется она по прямой: вперед или назад, вправо или влево. 
            К легким фигурам относят коней и слонов.
           
            Слон ходит только по диагоналям, вперед или назад. 
            У каждого из соперников по два слона: один ходит по белым полям, другой по черным.
            
            Конь это фигура с изюминкой. Только конь может двигаться на два поля вперед, 
            однок вбок или два вбок и одно вперед или назад. 
            По простому конь ходит буквой «Г». 
            Конь может перепрыгивать через фигуры и пешки, 
            поэтому всегда бдительно смотрите за конями. 
            Наибольшее количество возможных ходов 8 возможно лишь 
            когда конь находится в центре доски, 
            а наименьшее когда на краю. 
            Поэтом конь на краю доски зачастую занимает плохое положение.
            
            Пешка самая слабая фигура. Пешка ходит только вперед и только на один шаг, 
            кроме начального положения в котором она может пойти и на две клетки. 
            Бьет пешка по диагонали от себя на одну клетку. 
            При достижении пешкой последней горизонтали (8 линия для белых и 1 для черных) 
            пешка может превратиться в любую другую фигур вплоть до ферзя, 
            кроме как остаться просто пешкой.');


        $chessLessons[2]
            ->setName('Правила шахмат')
            ->setContent('1. «Взялся – ходи» - золотое и самое главное из правил. 
            Если игрок при своем ходе тронул свою фигуру, 
            он должен сделать ей ход (кроме случая при объявлении его королю шаха если он не 
            может закрыться или убить фигуру объявившую шаг, то имеет право пойти другой фигурой). 
            В случае если прикоснулся к фигуре противника, то должен взять ее.
            
            2. Если вы хотите поправить свою или чужую фигуру или фигуры, 
            вы должны сказать, четко и громко: «Поправляю», и только после этого касаться фигур(ы).
            
            3. Ход(ы) нельзя взять обратно.
            
            4. Пользоваться записями или печатными материалами, 
            советоваться с кем-то, консультироваться или общаться с 
            кем-либо кроме как с судьей или в его присутствии запрещается во время партии.
            
            5. Запрещено как-либо отвлекать или тревожить противника, 
            а также обращаться к нему, когда он думает над ходом.');


        $chessLessons[3]
            ->setName('Превращение пешки. Взятие на проходе')
            ->setContent('При вступлении пешки на последнюю горизонталь 
            (белая - восьмая линия, черная – первая) 
            она сразу превращается в любую фигуру своего цвета, кроме короля. 
            При этом не имеет значения, есть ли такая фигура на доске или нет.
            Простыми словами, выбор фигуры зависит от желания шахматиста.
            
            Если пешка, сделав ход со своей начальной позиции на два шага 
            (белые – со второго на четвертый ряд, черные - с седьмого на пятый), 
            переступит поле, которое находится под ударом неприятельской пешки, 
            то она может забрать проходящую пешку. 
            При этом бьющая пешка становится не на то поле, куда совершала прыжок пешка противника, 
            а на «битое» поле, через которое та перепрыгнула. 
            Это правило называется «взятием на проходе» и относится только к пешкам. 
            Стоит запомнить, что взятие на проходе может быть сделано только немедленно в ответ на двойной ход пешки. 
            Позже эта возможность теряется.');


        for ($i = 0; $i < 4; $i++) {
            $courses['pythonDeveloper']->addLesson($pythonLessons[$i]);
            $courses['layoutDesigner']->addLesson($layoutDesignerLessons[$i]);
            $courses['webDeveloper']->addLesson($webDeveloperLessons[$i]);
            $courses['chessPlayer']->addLesson($chessLessons[$i]);
            $courses['desktopDeveloper']->addLesson($desktopLessons[$i]);
        }

        foreach ($courses as $course) {
            $manager->persist($course);
        }

        $manager->flush();
    }
}
