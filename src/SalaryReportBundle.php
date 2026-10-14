<?php

declare(strict_types=1);

namespace kintai\Bundles\Installed\SalaryReport;

use kintai\Contract\Bundle;
use kintai\Contract\Extension\Action;
use kintai\Contract\Extension\NavItem;
use kintai\Core\Repositories\DatabaseSalaryReportRepository;
use kintai\Core\Repositories\SalaryReportRepositoryInterface;

/**
 * Comme ResignationReport/ShiftClaim/StorePhoto, aucun autre composant Core
 * ne dépend de SalaryReportRepositoryInterface : le repository est enregistré
 * par ce bundle, pas par le Core. Désactiver ou désinstaller "salary-report"
 * retire entièrement la fonctionnalité.
 *
 * Dépendance bundle→bundle : AdminSalaryReportController utilise
 * DailyReportRepositoryInterface (Core-bound, voir docs/architecture.md côté
 * Kintai) pour pré-remplir le chiffre d'affaires du mois depuis les rapports
 * journaliers validés/soumis (calculateSalaryPreset). Cette interface restant
 * liée par Kintai Core indépendamment du bundle "daily-report", elle se
 * résout toujours, que ce bundle soit installé ou non.
 */
final class SalaryReportBundle extends Bundle
{
    public function getName(): string
    {
        return 'salary-report';
    }

    public function getVersion(): string
    {
        return '1.1.0';
    }

    public function getLabel(): string
    {
        return __('bundle_salary_report');
    }

    public function getDescription(): string
    {
        return __('bundle_salary_report_desc');
    }

    public function register(): void
    {
        $this->registerServices();
        $this->loadViewsFrom($this->getPath() . '/Views', 'salary-report');
        $this->loadRoutesFrom($this->getPath() . '/routes.php');
        $this->loadAssetsFrom('public');
    }

    private function registerServices(): void
    {
        $container = $this->app->container();

        $container->singleton(
            SalaryReportRepositoryInterface::class,
            fn() => new DatabaseSalaryReportRepository()
        );
    }

    /**
     * Navigation (contrat 1.1) : entrée(s) de la barre du haut et de la barre mobile.
     */
    public function navigation(): array
    {
        return [
            new NavItem('salary_report', NavItem::REPORTS, 'salary_report', 'admin.reports.salary', 'admin.reports.salary',
                icon: 'money', activePaths: ['*/reports/salary'], order: 50),
        ];
    }

    /**
     * Liens dans les pages du Core (contrat 1.6).
     */
    public function actions(): array
    {
        // Lien « Rapport de salaire » vers le formulaire de création, pré-rempli pour l'employé quand il est connu.
        $salary = static fn(array $c): string => base_url() . '/admin/stores/' . (int) $c['store_id'] . '/reports/salary/create'
            . (isset($c['user_id']) ? '?user_id=' . (int) $c['user_id'] : '');

        return [
            new Action('salary_report', Action::EMPLOYEE_ROW, 'salary_report', $salary, '💰', permission: 'payroll.generate', order: 10),
            new Action('salary_report', Action::EMPLOYEE_STORE, 'salary_report', $salary, permission: 'payroll.generate', order: 20),
            new Action('salary_report', Action::STORE_REPORT, 'salary_report', static fn(array $c): string => $salary(['store_id' => $c['store_id']]), '💰', Action::PRIMARY, order: 10),
            new Action('salary_report', Action::STORE_REPORT_ROW, 'salary_report', $salary, '💰', order: 10),
        ];
    }
}
