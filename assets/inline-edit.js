jQuery(document).ready(function ($) {
  // Handle inline order input change
  $(".faqzin-order-input").on("change", function () {
    var input = $(this);
    var postId = input.data("post-id");
    var newOrder = input.val();
    var statusIcon = input.siblings(".faqzin-order-status");

    // Disable input while saving
    input.prop("disabled", true);

    // Show loading
    statusIcon
      .html(
        '<span class="dashicons dashicons-update" style="color: #999; animation: rotation 1s infinite linear;"></span>'
      )
      .show();

    // AJAX request
    $.ajax({
      url: faqzinAjax.ajax_url,
      type: "POST",
      data: {
        action: "faqzin_save_order",
        nonce: faqzinAjax.nonce,
        post_id: postId,
        order: newOrder,
      },
      success: function (response) {
        if (response.success) {
          // Show success
          statusIcon.html(
            '<span class="dashicons dashicons-yes" style="color: green;"></span>'
          );

          // Hide after 2 seconds
          setTimeout(function () {
            statusIcon.fadeOut();
          }, 2000);
        } else {
          // Show error
          statusIcon.html(
            '<span class="dashicons dashicons-no" style="color: red;"></span>'
          );
        }

        // Re-enable input
        input.prop("disabled", false);
      },
      error: function () {
        // Show error
        statusIcon.html(
          '<span class="dashicons dashicons-no" style="color: red;"></span>'
        );
        input.prop("disabled", false);
      },
    });
  });

  // Quick Edit functionality
  var $wp_inline_edit = inlineEditPost.edit;

  inlineEditPost.edit = function (id) {
    $wp_inline_edit.apply(this, arguments);

    var post_id = 0;
    if (typeof id == "object") {
      post_id = parseInt(this.getId(id));
    }

    if (post_id > 0) {
      var $row = $("#edit-" + post_id);
      var $post_row = $("#post-" + post_id);

      // Get the order value from the column
      var order = $post_row.find(".faqzin-order-input").val();

      // Set it in the Quick Edit form
      $row.find('input[name="menu_order"]').val(order);
    }
  };
});

// CSS for rotation animation
var style = document.createElement("style");
style.innerHTML =
  "@keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }";
document.head.appendChild(style);
