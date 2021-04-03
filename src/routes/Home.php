<?php

namespace DI\routes;

use DateTime;
use DI\Application;
use DI\interfaces\Test;
use DI\decorators\{
    Directive,
    Route, Title,
    Scripts, Stylesheets,
    View
};
use DI\enums\ViewEngines;

//#[Route('/')]
#[Title('Mon Titre global')]
#[Scripts(['https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js'])]
#[Stylesheets(['https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css'])]
class Home {
    public function __construct(
        private Test $test,
        private ?int $id = null
    ) {}

    #[Route('/')]
    #[Title('Mon Titre spécifique')]
    #[Stylesheets(['/assets/styles/style.css'])]
    public function get(Application $app): string {
        $app->setTest('toto');
        return 'pas d\'id => NULL ' . $this->test->myFunc($app);
    }

    #[Route('/test/([0-9]+)')] #[Title('get 2')]
    public function get2(Application $app): string {
        return <<<HTML
            <div class="container">
                coucou {$this->id}
            </div>
        HTML;
    }

    #[Route('/test')] #[View('test')]
    #[Directive('datetime', 'Directive::datetime')]

    #[Title('Titre de ma page avec un gestionnaire de templates')]
    public function test() {
        return [
            'test' => 'Nicolas',
            'birthday' => new DateTime('1989/08/19')
        ];
    }

    #[Route('/test-2')]
    #[View('test', ViewEngines::SMARTY)]
    public function test2() {
        return [
            'test' => 'Nicolas',
        ];
    }
}