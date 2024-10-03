jQuery(document).ready(function($)
{
  var email_selector = $("input[name=fakturpro_invoice_email]");
  var email_selected = $("input[name=fakturpro_invoice_email]:checked");
  var email_to_append_to = $('#fakturpro_email_to_append_to').closest('tr');
  var email_for_methods = $('#fakturpro_no_email_for_methods').closest('tr');
  var email_template = $("select[name=fakturpro_email_template]");
  var email_for_states = $('#fakturpro_email_for_states').closest('tr');
  var email_subject = $('#fakturpro_email_subject').closest('tr');
  var email_to = $('#fakturpro_email_to').closest('tr');
  var email_copy = $('#fakturpro_email_copy').closest('tr');
  var email_blind_copy = $('#fakturpro_email_blind_copy').closest('tr');
  var email_content_text = $('#fakturpro_email_content_text').closest('tr');
  var email_content_html = $('#fakturpro_email_content_html').closest('tr');
  var email_content_placeholders = $('#fakturpro_email_content_placeholders').closest('tr');

  var change_email_template_setting = function(selection) {
    switch (selection) {
      case "none":
        email_subject.show();
        email_to.show();
        email_copy.show();
        email_blind_copy.show();
        email_content_text.show();
        email_content_html.show();
        email_content_placeholders.show();
        break;
      default:
        email_subject.hide();
        email_to.hide();
        email_copy.hide();
        email_blind_copy.hide();
        email_content_text.hide();
        email_content_html.hide();
        email_content_placeholders.hide();
        break;
    }
  };

  var change_email_settings = function(selection) {
    if ("none" === selection)
    {
      email_to_append_to.hide();
      email_for_states.hide();
      email_for_methods.hide();
      email_template.hide();
      /*email_subject.hide();
      email_to.hide();
      email_copy.hide();
      email_blind_copy.hide();
      email_content_text.hide();
      email_content_html.hide();
      email_content_placeholders.hide();
      */
      change_email_template_setting("");
    }
    if ("append" === selection)
    {
      email_to_append_to.show();
      email_for_methods.show();
      email_for_states.hide();
      email_template.hide();
      /*email_subject.hide();
      email_to.hide();
      email_copy.hide();
      email_blind_copy.hide();
      email_content_text.hide();
      email_content_html.hide();
      email_content_placeholders.hide();*/
      change_email_template_setting("");
    }
    if ("separate" === selection)
    {
      email_to_append_to.hide();
      email_for_states.show();
      email_for_methods.show();
      email_template.show();
      /*email_subject.show();
      email_to.show();
      email_copy.show();
      email_blind_copy.show();
      email_content_text.show();
      email_content_html.show();
      email_content_placeholders.show();*/
      change_email_template_setting(email_template.val());
    };
  };

  email_template.change(function(e) {
    change_email_template_setting($(this).val());
  });
  email_selector.change(function(e) {
    change_email_settings($(this).val());
  });

  change_email_template_setting(email_template.val());
  change_email_settings(email_selected.val());
});
