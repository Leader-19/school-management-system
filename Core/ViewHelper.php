<?php

/**
 * Shared view helpers.
 *
 * Keeps small pieces of markup that appear on every page out of the views.
 */
class ViewHelper {
    /**
     * Hidden CSRF field. Must be present in every POST form, otherwise
     * CsrfMiddleware rejects the request.
     */
    public static function csrfField() {
        $token = CsrfMiddleware::token();
        return '<input type="hidden" name="' . CsrfMiddleware::FIELD . '" value="' . htmlspecialchars($token) . '">';
    }

    /**
     * Bootstrap colour for a grade/percentage.
     */
    public static function scoreColour($score) {
        $score = (float) $score;

        if ($score >= 75) {
            return 'success';
        }
        if ($score >= 50) {
            return 'warning';
        }
        return 'danger';
    }

    private const SOFT_TONES = [
        'success' => 'soft-success',
        'warning' => 'soft-warning',
        'danger'  => 'soft-danger',
    ];

    public static function scoreBadge($score) {
        $score = (float) $score;
        $tone = self::SOFT_TONES[self::scoreColour($score)];
        return '<span class="badge ' . $tone . '"><i class="fa-solid fa-star me-1"></i>'
            . htmlspecialchars((string) $score) . '</span>';
    }

    /**
     * Human readable "in N days" / "N days ago" for a due date.
     *
     * @return array{label:string, class:string, icon:string}
     */
    public static function dueDateState($dueDate) {
        $timestamp = strtotime((string) $dueDate);

        if ($timestamp === false) {
            return ['label' => 'No due date', 'class' => 'secondary', 'icon' => 'fa-regular fa-clock'];
        }

        $diff = $timestamp - time();
        $days = (int) ceil(abs($diff) / 86400);

        if ($diff < 0) {
            return [
                'label'  => $days <= 1 ? 'Overdue by 1 day' : "Overdue by {$days} days",
                'class'  => 'danger',
                'icon'   => 'fa-solid fa-triangle-exclamation'
            ];
        }

        if ($diff < 86400) {
            return ['label' => 'Due today', 'class' => 'warning', 'icon' => 'fa-solid fa-hourglass-half'];
        }

        if ($days <= 3) {
            return ['label' => "Due in {$days} days", 'class' => 'warning', 'icon' => 'fa-solid fa-clock'];
        }

        return ['label' => "Due in {$days} days", 'class' => 'primary', 'icon' => 'fa-regular fa-calendar'];
    }

    /**
     * Consistent empty-state block for tables and lists.
     */
    public static function emptyState($icon, $title, $message = '', $actionUrl = null, $actionLabel = '') {
        $html = '<div class="empty-state">'
            . '<span class="empty-state-icon"><i class="fa-solid ' . htmlspecialchars($icon) . '"></i></span>'
            . '<h6 class="empty-state-title">' . htmlspecialchars($title) . '</h6>';

        if ($message !== '') {
            $html .= '<p class="empty-state-text">' . htmlspecialchars($message) . '</p>';
        }

        if ($actionUrl) {
            $html .= '<a href="' . htmlspecialchars($actionUrl) . '" class="btn btn-sm btn-primary mt-3">'
                . '<i class="fa-solid fa-plus me-1"></i> ' . htmlspecialchars($actionLabel ?: 'Create one') . '</a>';
        }

        return $html . '</div>';
    }
}