(function ($) {
    'use strict';

    function setupFaqzin($container) {
        $container.attr('role', 'list');
        $container.find('.faqzin-item').each(function () {
            var $item = $(this);
            var $question = $item.find('.faqzin-question').first();
            var $answer = $item.find('.faqzin-answer').first();

            if (!$question.length || !$answer.length) {
                return;
            }

            $answer.attr({
                'role': 'region',
                'aria-hidden': 'true'
            });

            $question.attr({
                'tabindex': '0',
                'role': 'button',
                'aria-expanded': 'false'
            });

            function toggle(open) {
                var isOpen = open !== undefined ? open : $answer.hasClass('is-open') === false;
                $question.toggleClass('is-open', isOpen);
                $answer.toggleClass('is-open', isOpen);
                $answer.attr('aria-hidden', isOpen ? 'false' : 'true');
                $question.attr('aria-expanded', isOpen ? 'true' : 'false');
            }

            $question.on('click', function () {
                toggle();
            });

            $question.on('keydown', function (event) {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    toggle();
                }
            });
        });
    }

    $(function () {
        $('.faqzin').each(function () {
            setupFaqzin($(this));
        });
    });
})(jQuery);
