(function ($) {
    'use strict';

    function setupFaqzin($container) {
        $container.attr('role', 'list');
        $container.find('details.faqzin-item').each(function () {
            var $item = $(this);
            var $question = $item.find('.faqzin-question').first();
            var $answer = $item.find('.faqzin-answer').first();

            if (!$question.length || !$answer.length) {
                return;
            }

            function syncState() {
                var isOpen = $item.prop('open');
                $item.toggleClass('is-open', isOpen);
                $question.toggleClass('is-open', isOpen);
                $answer.toggleClass('is-open', isOpen);
                $question.attr('aria-expanded', isOpen ? 'true' : 'false');
                $answer.attr('aria-hidden', isOpen ? 'false' : 'true');
            }

            syncState();

            $item.on('toggle', function () {
                syncState();
            });
        });
    }

    $(function () {
        $('.faqzin').each(function () {
            setupFaqzin($(this));
        });
    });
})(jQuery);
