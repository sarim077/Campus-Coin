@props([
    'currentValue' => 0,
    'goalValue' => null,
    'label' => 'Goal progress',
    'color' => 'var(--theme-progress)',
    'currencySymbol' => '$',
])

@php
    $currentIsNumeric = is_numeric($currentValue) && is_finite((float) $currentValue);
    $goalIsNumeric = is_numeric($goalValue) && is_finite((float) $goalValue);
    $current = $currentIsNumeric ? (float) $currentValue : 0.0;
    $goal = $goalIsNumeric ? (float) $goalValue : 0.0;
    $hasGoal = $goalIsNumeric && $goal > 0;
    $percentage = $hasGoal ? min(100, max(0, ($current / $goal) * 100)) : 0;
    $hasProgress = $hasGoal && $current > 0;
@endphp

<div class="goal-thermometer" style="--goal-thermometer-color: {{ $color }}; --goal-thermometer-level: {{ $percentage }}%;">
    <span class="goal-thermometer__label">{{ $label }}</span>
    <div
        class="goal-thermometer__visual{{ $hasProgress ? ' goal-thermometer--active' : '' }}"
        role="meter"
        aria-label="{{ $label }}"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-valuenow="{{ number_format($percentage, 1, '.', '') }}"
        aria-valuetext="{{ number_format($percentage, 0) }}%"
    >
        <span class="goal-thermometer__tube" aria-hidden="true">
            <span class="goal-thermometer__fill"></span>
        </span>
        <span class="goal-thermometer__bulb" aria-hidden="true"></span>
    </div>
    <div class="goal-thermometer__values">
        <strong>{{ $currencySymbol }}{{ number_format($current, 2) }}</strong>
        @if($hasGoal)
            <span>of</span>
            <strong>{{ $currencySymbol }}{{ number_format($goal, 2) }}</strong>
        @else
            <span class="goal-thermometer__prompt">Set a goal to track progress</span>
        @endif
    </div>
</div>
