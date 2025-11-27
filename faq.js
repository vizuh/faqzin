(function ($) {
     'use strict';

     function initFaqAccordion() {
          $('.faqzin-item').each(function () {
               var $item = $(this);

               // Skip if already initialized
               if ($item.data('faqzin-initialized')) {
                    return;
               }

               var $summary = $item.find('summary');
               var $answer = $item.find('.faqzin-answer');
               var $icon = $item.find('.faqzin-icon');

               // Sync initial state
               function syncState() {
                    var isOpen = $item.prop('open');
                    $summary.attr('aria-expanded', isOpen ? 'true' : 'false');
                    $answer.attr('aria-hidden', isOpen ? 'false' : 'true');
                    $icon.text(isOpen ? '−' : '+');
               }

               syncState();

               // Listen to native toggle event from <details>
               $item.on('toggle', function () {
                    syncState();
               });

               // Mark as initialized
               $item.data('faqzin-initialized', true);
          });
     }

     // Initialize on ready
     $(document).ready(function () {
          initFaqAccordion();
     });

     // Reinitialize on load for cached content
     $(window).on('load', function () {
          initFaqAccordion();
     });

})(jQuery);
