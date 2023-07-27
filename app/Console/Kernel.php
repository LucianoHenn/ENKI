<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Carbon\Carbon;
use Illuminate\Support\Facades\App;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('horizon:snapshot')->everyFiveMinutes();

        $schedule->command('client-reports:process')->everyFiveMinutes()->withoutOverlapping();

        $schedule->command('currency_conversion:run')->dailyAt('05:00');
        $schedule->command('currency_conversion:run 90d')->dailyAt('20:00');

        $schedule->command('yads-general-report:process')
            ->everyFiveMinutes()
            ->between('8:00', '15:00')
            ->withoutOverlapping();


        // $schedule->command('iac-d2s-general-report:process')
        //     ->everyFiveMinutes()
        //     ->between('10:30', '21:00')
        //     ->withoutOverlapping();

        // $schedule->command('iac-cost-general-report:process')
        //     ->everyFiveMinutes()
        //     ->between('9:00', '15:00')
        //     ->withoutOverlapping();

        if (App::environment('production')) {
            //Client CSV Reports
            //ZACH
            //yads-epnr-report:process --client_code=5426058655 --emails_addresses="mauro@mforward.it,marcello@bidberrymedia.com
            // $schedule->command('yads-epnr-report:process --client_code=5426058655 --emails_addresses="zach@pierviewmedia.com,masosnows@gmail.com,marcello@bidberrymedia.com,santi@bidberrymedia.com,mauro@bidberrymedia.com,obum@pierviewmedia.com"')
            //     ->everyFiveMinutes()
            //     ->between('8:00', '15:00')
            //     ->withoutOverlapping();

            //adexpertmedia (AEM)
            // $schedule->command('yads-epnr-report:process --client_code=1649868420 --emails_addresses="marcello@bidberrymedia.com,santi@bidberrymedia.com,mauro@bidberrymedia.com,reports@adexpertsmedia.com"')
            //     ->everyFiveMinutes()
            //     ->between('8:00', '15:00')
            //     ->withoutOverlapping();

            // //QLTYMEdia
            // $schedule->command('yads-epnr-report:process --client_code=2978120963 --emails_addresses="marcello@bidberrymedia.com,santi@bidberrymedia.com,mauro@bidberrymedia.com,admin@qltymedia.com"')
            // ->everyFiveMinutes()
            // ->between('8:00', '15:00')
            // ->withoutOverlapping();

            //ORGANIK MEDIA
            /*
            $schedule->command('yads-epnr-report:process --client_code=2358236092 --emails_addresses="alliance@organik-media.com,marcello@bidberrymedia.com,santi@bidberrymedia.com,mauro@bidberrymedia.com"')
                ->everyFiveMinutes()
                ->between('8:00', '15:00')
		->withoutOverlapping();
	     */
            //FrontStory
            $schedule->command('yads-epnr-report:process --client_code=3953930329 --emails_addresses="saminahmiyaz@gmail.com,larabenoraya@gmail.com,marcello@bidberrymedia.com,santi@bidberrymedia.com,mauro@bidberrymedia.com"')
                ->everyFiveMinutes()
                ->between('8:00', '15:00')
                ->withoutOverlapping();


            // //DoXaClicks
            // $schedule->command('yads-epnr-report:process --client_code=4174416309 --emails_addresses="adv@doxaclick.com,marcello@bidberrymedia.com,santi@bidberrymedia.com,mauro@bidberrymedia.com,reports@doxaclick.com"')
            //     ->everyFiveMinutes()
            //     ->between('8:00', '15:00')
            //     ->withoutOverlapping();

            //showCaseAds
            // $schedule->command('yads-epnr-report:process --client_code=8542835953 --emails_addresses="rotem@showcasead.com,marcello@bidberrymedia.com,santi@bidberrymedia.com,mauro@bidberrymedia.com"')
            // ->everyFiveMinutes()
            // ->between('8:00', '15:00')
            // ->withoutOverlapping();


            // Gasmobi
            $schedule->command('yads-epnr-report:process --client_code=1872231371 --emails_addresses="f.zwanck@gasmobi.com,mcarrasco@gasmobi.com"')
                ->everyFiveMinutes()
                ->between('8:00', '20:00')
                ->withoutOverlapping();

            /*
            //WinkLeads
            $schedule->command('yads-epnr-report:process --client_code=4668993387 --emails_addresses="[TO PUT ADDRESS HERE]"')
            ->everyFiveMinutes()
            ->between('8:00',
                '20:00'
            )
            ->withoutOverlapping();
            */
        }


        //Booster
        if (config('booster.run_scheduler')) {
            //YADS
            $schedule->command('booster:process-keywords-stats yads')->cron('05 */4 * * *');
            $schedule->command('booster:process-keywords yads')->cron('35 */4 * * *');
        }


        //ARC REPORTS
        $today = Carbon::today()->format('Y-m-d');
        $yesterday = Carbon::yesterday()->format('Y-m-d');


        //Yahoo Daily
        $schedule->command("arc:report-process download import --source=Yahoo --type=Daily --date=$yesterday")
            ->everyTenMinutes()
            ->between('6:00', '23:00')
            ->withoutOverlapping();

        //BingAdsRevenue Daily
        $schedule->command("arc:report-process download import --source=BingAdsRevenue --type=Daily --date=$today")
            ->everyTenMinutes()
            ->between('4:00', '23:00')
            ->withoutOverlapping();


        $schedule->command("arc:report-process download --source=BingAdsRevenue --type=Daily --force --date=$today")
            ->cron('10 9 * * *');

        $schedule->command("arc:report-process download --source=BingAdsRevenue --type=Daily --force --date=$today")
            ->between('10:00', '17:30')
            ->hourly()
            ->withoutOverlapping();


        $schedule->command("arc:report-process download --source=BingAdsRevenue --type=Daily --force --date=$today")
            ->dailyAt('20:00');
        //BingAdsRevenue Daily

        //AfsByCbs Daily
        $schedule->command("arc:report-process download import --source=AfsByCbs --type=Daily --date=$yesterday")
            ->everyTenMinutes()
            ->between('7:30', '23:00')
            ->withoutOverlapping();


        $schedule->command("arc:report-process download --source=AfsByCbs --type=Daily --force --date=$yesterday")
            ->dailyAt('8:15');

        $schedule->command("arc:report-process download --source=AfsByCbs --type=Daily --force --date=$yesterday")
            ->dailyAt('9:00');


        $schedule->command("arc:report-process download --source=AfsByCbs --type=Daily --force --date=$yesterday")
            ->dailyAt('10:00');

        $schedule->command("arc:report-process download --source=AfsByCbs --type=Daily --force --date=$yesterday")
            ->dailyAt('13:00');
        //AfsByCbs Daily

        // //IAC d2s
        // $schedule->command("arc:report-process download import --source=IAC --type=D2S --date=$yesterday")
        //     ->everyTenMinutes()
        //     ->between('10:00', '18:00')
        //     ->withoutOverlapping();
        // //IAC d2s


        //Yahoo Hourly
        $schedule->command('arc:report-process --type=Hourly --source=Yahoo download import --date=' . $today . ' --force')
            ->cron('5 * * * *');
        $schedule->command('arc:report-process --type=Hourly --source=Yahoo download import --date=' . $today)
            ->cron('*/13 * * * *');

        //BingAds Campaigns
        $schedule->command("arc:report-process download import --source=BingAds --type=Campaigns --date=$yesterday")
            ->everyTenMinutes()
            ->between('00:05', '09:00')
            ->withoutOverlapping();

        //BingAds CampaignPerformance
        $schedule->command("arc:report-process download import --source=BingAds --type=Campaignperformance --date=$yesterday")
            ->everyTenMinutes()
            ->between('4:00', '18:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=BingAds --type=Campaignperformance --date=$yesterday --force")
            ->dailyAt('9:00')
            ->withoutOverlapping();


        //BingAds CampaignPerformance Daily consolidation
        $timeDelta = Carbon::now()->subDays(2)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=BingAds --type=Campaignperformance --date=$timeDelta --force")
            ->dailyAt('22:00');

        $timeDelta = Carbon::now()->subDays(8)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=BingAds --type=Campaignperformance --date=$timeDelta --force")
            ->dailyAt('20:00');

        $timeDelta = Carbon::now()->subDays(20)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=BingAds --type=Campaignperformance --date=$timeDelta --force")
            ->dailyAt('18:30');


        //BingAds KeywordPerformance
        $schedule->command("arc:report-process download import --source=BingAds --type=Keywordperformance --date=$yesterday")
            ->everyTenMinutes()
            ->between('4:00', '18:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=BingAds --type=Keywordperformance --date=$yesterday --force")
            ->dailyAt('9:00')
            ->withoutOverlapping();

        //BingAds KeywordPerformance Daily consolidation
        $timeDelta = Carbon::now()->subDays(2)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=BingAds --type=Keywordperformance --date=$timeDelta --force")
            ->dailyAt('22:00');

        $timeDelta = Carbon::now()->subDays(8)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=BingAds --type=Keywordperformance --date=$timeDelta --force")
            ->dailyAt('20:00');

        $timeDelta = Carbon::now()->subDays(20)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=BingAds --type=Keywordperformance --date=$timeDelta --force")
            ->dailyAt('18:30');

        //Facebook Campaigns
        $schedule->command("arc:report-process download import --source=Facebook --type=Campaigns --date=$yesterday")
            ->everyTenMinutes()
            ->between('00:05', '09:00')
            ->withoutOverlapping();
        //Facebook AdSets
        $schedule->command("arc:report-process download import --source=Facebook --type=AdSets --date=$yesterday")
            ->everyTenMinutes()
            ->between('00:05', '09:00')
            ->withoutOverlapping();


        //Facebook AdCreative
        $schedule->command("arc:report-process import --source=Facebook --type=AdCreative")
            ->everyTenMinutes()
            ->withoutOverlapping();

        //Facebook AdCreative
        $schedule->command("arc:report-process download import --source=Facebook --type=AdCreative --date=$today")
            ->everyTwoHours()
            ->withoutOverlapping();

        //Facebook Adperformance
        $schedule->command("arc:report-process import --source=Facebook --type=Adperformance")
            ->everyTenMinutes()
            ->withoutOverlapping();

        $schedule->command("arc:report-process download --source=Facebook --type=Adperformance --date=$yesterday")
            ->everyTenMinutes()
            ->between('3:50', '23:30')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download --source=Facebook --type=Adperformance --date=$yesterday --force")
            ->dailyAt('06:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download --source=Facebook --type=Adperformance --date=$yesterday --force")
            ->dailyAt('09:00')
            ->withoutOverlapping();

        //Facebook PST REDOWNLOAD
        $schedule->command("arc:report-process download --source=Facebook --type=Adperformance --date=$yesterday --force")
            ->dailyAt('14:00')
            ->withoutOverlapping();

        //Facebook Adperformance Daily consolidation
        $schedule->command("arc:report-process download --source=Facebook --type=Adperformance --date=$yesterday --force")
            ->dailyAt('22:00')
            ->withoutOverlapping();

        $timeDelta = Carbon::now()->subDays(2)->format('Y-m-d');
        $schedule->command("arc:report-process download --source=Facebook --type=Adperformance --date=$timeDelta --force")
            ->dailyAt('23:00');

        $timeDelta = Carbon::now()->subDays(8)->format('Y-m-d');
        $schedule->command("arc:report-process download --source=Facebook --type=Adperformance --date=$timeDelta --force")
            ->dailyAt('20:00');

        $timeDelta = Carbon::now()->subDays(20)->format('Y-m-d');
        $schedule->command("arc:report-process download --source=Facebook --type=Adperformance --date=$timeDelta --force")
            ->dailyAt('18:30');




        //GoogleAds Campaigns
        $schedule->command("arc:report-process download import --source=GoogleAds --type=Campaigns --date=$yesterday")
            ->everyTenMinutes()
            ->between('00:05', '09:00')
            ->withoutOverlapping();

        //GoogleAds Adperformance
        $schedule->command("arc:report-process download import --source=GoogleAds --type=Adperformance --date=$yesterday")
            ->everyTenMinutes()
            ->between('4:00', '23:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=GoogleAds --type=Adperformance --date=$yesterday --force")
            ->dailyAt('9:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=GoogleAds --type=Adperformance --date=$yesterday --force")
            ->dailyAt('13:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=GoogleAds --type=Adperformance --date=$yesterday --force")
            ->dailyAt('16:00')
            ->withoutOverlapping();


        //GoogleAds Adperformance Daily consolidation
        $timeDelta = Carbon::now()->subDays(2)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=GoogleAds --type=Adperformance --date=$timeDelta --force")
            ->dailyAt('22:00');

        $timeDelta = Carbon::now()->subDays(8)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=GoogleAds --type=Adperformance --date=$timeDelta --force")
            ->dailyAt('20:00');

        $timeDelta = Carbon::now()->subDays(15)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=GoogleAds --type=Adperformance --date=$timeDelta --force")
            ->dailyAt('18:30');

        $timeDelta = Carbon::now()->subDays(20)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=GoogleAds --type=Adperformance --date=$timeDelta --force")
            ->dailyAt('19:30');


        //GoogleAds Keywordperformance
        $schedule->command("arc:report-process download import --source=GoogleAds --type=Keywordperformance --date=$yesterday")
            ->everyTenMinutes()
            ->between('4:00', '23:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=GoogleAds --type=Keywordperformance --date=$yesterday --force")
            ->dailyAt('9:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=GoogleAds --type=Keywordperformance --date=$yesterday --force")
            ->dailyAt('13:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=GoogleAds --type=Keywordperformance --date=$yesterday --force")
            ->dailyAt('16:00')
            ->withoutOverlapping();


        //GoogleAds Keywordperformance Daily consolidation
        $timeDelta = Carbon::now()->subDays(2)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=GoogleAds --type=Keywordperformance --date=$timeDelta --force")
            ->dailyAt('22:00');

        $timeDelta = Carbon::now()->subDays(8)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=GoogleAds --type=Keywordperformance --date=$timeDelta --force")
            ->dailyAt('20:00');

        $timeDelta = Carbon::now()->subDays(20)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=GoogleAds --type=Keywordperformance --date=$timeDelta --force")
            ->dailyAt('18:30');

        $timeDelta = Carbon::now()->subDays(15)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=GoogleAds --type=Keywordperformance --date=$timeDelta --force")
            ->dailyAt('19:30');



        //Taboola Daily
        $schedule->command("arc:report-process download import --source=Taboola --type=Daily --date=$yesterday")
            ->everyTenMinutes()
            ->between('4:00', '18:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=Taboola --type=Daily --date=$yesterday --force")
            ->dailyAt('9:00')
            ->withoutOverlapping();


        //Taboola Daily Daily consolidation
        $timeDelta = Carbon::now()->subDays(2)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Taboola --type=Daily --date=$timeDelta --force")
            ->dailyAt('22:00');

        $timeDelta = Carbon::now()->subDays(8)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Taboola --type=Daily --date=$timeDelta --force")
            ->dailyAt('20:00');

        $timeDelta = Carbon::now()->subDays(20)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Taboola --type=Daily --date=$timeDelta --force")
            ->dailyAt('18:30');

        //System1 Subid
        $schedule->command("arc:report-process download import --source=System1 --type=Subid --date=$yesterday")
            ->everyTenMinutes()
            ->between('7:00', '18:00')
            ->withoutOverlapping();


        //System1 Subid - Daily consolidation
        $timeDelta = Carbon::now()->subDays(2)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=System1 --type=Subid --date=$timeDelta --force")
            ->dailyAt('22:00');

        $timeDelta = Carbon::now()->subDays(8)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=System1 --type=Subid --date=$timeDelta --force")
            ->dailyAt('20:00');


        //ExploreAds
        $schedule->command("arc:report-process download import --source=ExploreAds --type=Daily --date=$yesterday")
            ->everyTenMinutes()
            ->between('6:00', '19:00')
            ->withoutOverlapping();

        $timeDelta = Carbon::now()->subDays(3)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=ExploreAds --type=Daily --date=$timeDelta --force")
            ->dailyAt('01:10');

        $timeDelta = Carbon::now()->subDays(5)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=ExploreAds --type=Daily --date=$timeDelta --force")
            ->dailyAt('16:00');

        $timeDelta = Carbon::now()->subDays(20)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=ExploreAds --type=Daily --date=$timeDelta --force")
            ->dailyAt('18:30');
        //\ExploreAds

        //Tonic
        //Tonic Daily
        $schedule->command("arc:report-process download import --source=Tonic --type=Daily --date=$yesterday")
            ->everyTenMinutes()
            ->between('2:00', '18:00')
            ->withoutOverlapping();


        $schedule->command("arc:report-process download import --source=Tonic --type=Daily --date=$yesterday --force")
            ->dailyAt('6:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=Tonic --type=Daily --date=$yesterday --force")
            ->dailyAt('11:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=Tonic --type=Daily --date=$yesterday --force")
            ->dailyAt('15:00')
            ->withoutOverlapping();


        //Tonic Daily Daily consolidation
        $timeDelta = Carbon::now()->subDays(2)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Tonic --type=Daily --date=$timeDelta --force")
            ->dailyAt('01:00');

        //Tonic Daily Daily consolidation
        $timeDelta = Carbon::now()->subDays(2)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Tonic --type=Daily --date=$timeDelta --force")
            ->dailyAt('11:10');


        $timeDelta = Carbon::now()->subDays(3)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Tonic --type=Daily --date=$timeDelta --force")
            ->dailyAt('01:10');

        $timeDelta = Carbon::now()->subDays(3)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Tonic --type=Daily --date=$timeDelta --force")
            ->dailyAt('01:20');

        $timeDelta = Carbon::now()->subDays(5)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Tonic --type=Daily --date=$timeDelta --force")
            ->dailyAt('16:00');

        $timeDelta = Carbon::now()->subDays(20)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Tonic --type=Daily --date=$timeDelta --force")
            ->dailyAt('18:30');




        //TIKTOK
        //TikTok Daily
        $schedule->command("arc:report-process download import --source=TikTok --type=Daily --date=$yesterday")
            ->everyTenMinutes()
            ->between('4:00', '18:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=TikTok --type=Daily --date=$yesterday --force")
            ->dailyAt('9:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=TikTok --type=Daily --date=$yesterday --force")
            ->dailyAt('19:00')
            ->withoutOverlapping();


        //TikTok Daily Daily consolidation
        $timeDelta = Carbon::now()->subDays(2)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=TikTok --type=Daily --date=$timeDelta --force")
            ->dailyAt('22:00');

        $timeDelta = Carbon::now()->subDays(8)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=TikTok --type=Daily --date=$timeDelta --force")
            ->dailyAt('20:00');

        $timeDelta = Carbon::now()->subDays(20)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=TikTok --type=Daily --date=$timeDelta --force")
            ->dailyAt('18:30');

        $schedule->command("arc:report-process download import --source=TikTok --type=Campaigns --date=$yesterday")
            ->everyTenMinutes()
            ->between('00:05', '09:00')
            ->withoutOverlapping();
        $schedule->command("arc:report-process download import --source=TikTok --type=AdGroups --date=$yesterday")
            ->everyTenMinutes()
            ->between('00:05', '09:00')
            ->withoutOverlapping();


        //ZEMANTA
        //Zemanta Daily
        $schedule->command("arc:report-process download import --source=Zemanta --type=Daily --date=$yesterday")
            ->everyTenMinutes()
            ->between('3:00', '23:30')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=Zemanta --type=Daily --date=$yesterday --force")
            ->dailyAt('5:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=Zemanta --type=Daily --date=$yesterday --force")
            ->dailyAt('7:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=Zemanta --type=Daily --date=$yesterday --force")
            ->dailyAt('9:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=Zemanta --type=Daily --date=$yesterday --force")
            ->dailyAt('12:30')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=Zemanta --type=Daily --date=$yesterday --force")
            ->dailyAt('19:00')
            ->withoutOverlapping();


        //Zemanta Daily Daily consolidation
        $timeDelta = Carbon::now()->subDays(3)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Zemanta --type=Daily --date=$timeDelta --force")
            ->dailyAt('2:00');

        $timeDelta = Carbon::now()->subDays(2)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Zemanta --type=Daily --date=$timeDelta --force")
            ->dailyAt('2:15');

        $timeDelta = Carbon::now()->subDays(8)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Zemanta --type=Daily --date=$timeDelta --force")
            ->dailyAt('2:20');

        $timeDelta = Carbon::now()->subDays(20)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Zemanta --type=Daily --date=$timeDelta --force")
            ->dailyAt('2:25');

        $schedule->command("arc:report-process download import --source=Zemanta --type=Campaigns --date=$yesterday")
            ->everyTenMinutes()
            ->between('00:05', '09:00')
            ->withoutOverlapping();



        //OUTBRAIN
        //Outbrain Daily
        $schedule->command("arc:report-process download import --source=Outbrain --type=Daily --date=$yesterday")
            ->everyTenMinutes()
            ->between('3:00', '21:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=Outbrain --type=Daily --date=$yesterday --force")
            ->dailyAt('5:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=Outbrain --type=Daily --date=$yesterday --force")
            ->dailyAt('7:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=Outbrain --type=Daily --date=$yesterday --force")
            ->dailyAt('9:00')
            ->withoutOverlapping();

        $schedule->command("arc:report-process download import --source=Outbrain --type=Daily --date=$yesterday --force")
            ->dailyAt('19:00')
            ->withoutOverlapping();


        //Outbrain Daily Daily consolidation
        $timeDelta = Carbon::now()->subDays(2)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Outbrain --type=Daily --date=$timeDelta --force")
            ->dailyAt('22:00');

        $timeDelta = Carbon::now()->subDays(8)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Outbrain --type=Daily --date=$timeDelta --force")
            ->dailyAt('20:00');

        $timeDelta = Carbon::now()->subDays(20)->format('Y-m-d');
        $schedule->command("arc:report-process download import --source=Outbrain --type=Daily --date=$timeDelta --force")
            ->dailyAt('18:30');


        // run import:facebook-pages command hourly
        $schedule->command('import:facebook-pages')
            ->hourly()
            ->withoutOverlapping();

        // run import:facebook-ad-accounts command in every 4 hours
        $schedule->command('import:facebook-ad-accounts')
            ->everyFourHours()
            ->withoutOverlapping();

        // run import:facebook-pixels command hourly
        $schedule->command('import:facebook-pixels')
            ->hourly()
            ->withoutOverlapping();




        //JOBBERS
        // dispatchers
        if (!config('jobber.dispatch')) {
            $schedule->command('jobber:dispatch')->everyFiveMinutes()->withoutOverlapping();
        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
