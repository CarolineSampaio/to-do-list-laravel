<?php

use App\Jobs\DeleteOldCompletedTasks;
use Illuminate\Support\Facades\Schedule;

Schedule::job(new DeleteOldCompletedTasks())->daily();
