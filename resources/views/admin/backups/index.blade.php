@extends('layouts.admin')
@section('title', 'Backups')
@section('content')
@php
    $partLabels = ['database' => 'Database', 'storage' => 'Storage', 'code' => 'Codebase'];
    $partIcons = ['database' => 'database', 'storage' => 'folder', 'code' => 'code'];
    $typeCards = [
        'database' => ['icon' => 'database', 'tint' => 'text-sky-500 bg-sky-50 dark:bg-sky-900/30', 'text' => 'Every table, structure and rows, as a restorable SQL dump.', 'size' => $sources['database']],
        'storage' => ['icon' => 'folder', 'tint' => 'text-emerald-500 bg-emerald-50 dark:bg-emerald-900/30', 'text' => 'Uploads and generated files under storage/app.', 'size' => $sources['storage']],
        'code' => ['icon' => 'code', 'tint' => 'text-violet-500 bg-violet-50 dark:bg-violet-900/30', 'text' => 'Application source, config and routes — no vendor or node_modules.', 'size' => $sources['code']],
        'full' => ['icon' => 'server', 'tint' => 'text-amber-500 bg-amber-50 dark:bg-amber-900/30', 'text' => 'Database, storage and codebase in one archive.', 'size' => $sources['database'] + $sources['storage'] + $sources['code']],
    ];
    $originLabels = [
        'manual' => 'Created by hand', 'scheduled' => 'Automatic', 'safety' => 'Safety copy',
        'upload' => 'Uploaded', 'imported' => 'Found on disk',
    ];
    $statusLabels = ['completed' => 'Ready', 'failed' => 'Failed', 'running' => 'In progress', 'pending' => 'Queued'];
    $alertTones = [
        'error' => ['bg-red-50 border-red-200 dark:bg-red-900/25 dark:border-red-900/40', 'text-red-600 dark:text-red-300'],
        'warning' => ['bg-amber-50 border-amber-200 dark:bg-amber-900/25 dark:border-amber-900/40', 'text-amber-600 dark:text-amber-300'],
        'info' => ['bg-sky-50 border-sky-200 dark:bg-sky-900/25 dark:border-sky-900/40', 'text-sky-600 dark:text-sky-300'],
    ];
    $input = 'w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-900/50 dark:text-white shadow-sm brand-ring focus:border-primary text-sm px-3.5 py-2.5';
    $label = 'block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1.5';
    $activeSchedules = $schedules->where('is_active', true)->count();
@endphp
@include('admin.backups._part_a')
@include('admin.backups._part_b')
