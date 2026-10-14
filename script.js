$(function () {
  var $menuToggle = $('.menu-toggle');
  var $navLinks = $('.nav-links');

  $menuToggle.on('click', function () {
    var isOpen = $navLinks.toggleClass('open').hasClass('open');
    $menuToggle.attr('aria-expanded', String(isOpen)).text(isOpen ? '×' : '☰');
  });

  $navLinks.find('a').on('click', function () {
    $navLinks.removeClass('open');
    $menuToggle.attr('aria-expanded', 'false').text('☰');
  });

  $('[data-year]').text(new Date().getFullYear());

  var $leadForm = $('form[data-lead-form]');
  if (!$leadForm.length) return;

  var $submit = $leadForm.find('button[type="submit"]');
  var $status = $('<p class="form-note" role="status" aria-live="polite"></p>').hide();
  $leadForm.find('.form-grid').prepend($status);

  $leadForm.on('submit', function (event) {
    event.preventDefault();
    var form = this;
    if (!form.checkValidity()) {
      form.reportValidity();
      return;
    }

    var payload = {};
    $.each($(form).serializeArray(), function (_, field) {
      payload[field.name] = field.value;
    });

    var isLocal = ['localhost', '127.0.0.1'].indexOf(window.location.hostname) !== -1 || window.location.protocol === 'file:';
    if (isLocal) {
      var localMessage = [
        'Hello Aurelia, I would like to learn more about the club.', '',
        'Name: ' + (payload.name || ''),
        'Email: ' + (payload.email || ''),
        'Interest: ' + (payload.interest || 'General enquiry'),
        'Message: ' + (payload.message || 'None')
      ].join('\n');
      $status.text('Local preview: PHP is not running here. Opening WhatsApp instead.').show();
      window.open('https://wa.me/923000000000?text=' + encodeURIComponent(localMessage), '_blank', 'noopener,noreferrer');
      return;
    }

    $submit.prop('disabled', true).text('Sending…');
    $status.hide();
    $.ajax({
      url: $leadForm.attr('action') || 'submit.php',
      method: 'POST',
      data: $(form).serialize(),
      dataType: 'json'
    }).done(function (response) {
      if (!response.success) {
        $status.text(response.message || 'Something went wrong. Please try again.').show();
        return;
      }
      form.reset();
      $status.text(response.message || 'Your enquiry was saved.').show();
      if (response.whatsapp_url) {
        window.location.href = response.whatsapp_url;
      }
    }).fail(function (xhr) {
      var message = 'The request could not be sent. Please try WhatsApp or call the club.';
      if (xhr.responseJSON && xhr.responseJSON.message) message = xhr.responseJSON.message;
      $status.text(message).show();
    }).always(function () {
      $submit.prop('disabled', false).text('Send enquiry');
    });
  });
});
