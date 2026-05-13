<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('logs:clean')->dailyAt('02:00');

Schedule::command('comprobantes:process-emails')
  ->everyFiveMinutes()
  ->withoutOverlapping();

Schedule::command('exchange-rate:update')->hourly();

Schedule::command('hacienda:poll-status')
  ->everyTenMinutes()
  ->withoutOverlapping();
