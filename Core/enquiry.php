   <div class="col-md-4">
             <div class="p-30 mt-0 bg-theme-colored">
              <h3 class="title-pattern mt-0"><span class="text-white">Request <span class="text-theme-color-2">Enquiry</span></span></h3>
              <!-- Appilication Form Start-->
              <form id="reservation_form" name="reservation_form" class="reservation-form mt-20" method="post" action="#">
                <div class="row">
                  <div class="col-sm-12">
                    <div class="form-group mb-20">
                      <input placeholder="Enter Name" type="text" id="reservation_name" name="reservation_name" required="" class="form-control">
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group mb-20">
                      <input placeholder="Email" type="text" id="reservation_email" name="reservation_email" class="form-control" required="">
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group mb-20">
                      <input placeholder="Phone" type="text" id="reservation_phone" name="reservation_phone" class="form-control" required="">
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group mb-20">
                      <div class="styled-select">
                        <select id="person_select" name="person_select" class="form-control" required="">
                          <option value="">Choose Subject</option>
                          <option value="dca"> DCA</option>
                          <option value="tally">TALLY</option>
                          <option value="adca">ADCA</option>
                          <option value="dtp">DTP</option>
                          <option value="ADVANCE EXCEL">ADVANCE EXCEL</option>
                          <option value="DIGITAL MARKETING">DIGITAL MARKETING</option>
                          <option value="CORELDRAW">CORELDRAW</option>
                          <option value="POWER BI">POWER BI</option>
                          <option value="Photoshop">PHOTOSHOP</option>
                          <option value="SHORTHAND">SHORTHAND</option>
                        </select>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <div class="form-group mb-20">
                      <input name="Date Of Birth" class="form-control required date-picker" type="text" placeholder="Date Of Birth" aria-required="true">
                    </div>
                  </div>
                  <div class="col-sm-12">
                    <div class="form-group">
                      <textarea placeholder="Enter Message" rows="3" class="form-control required" name="form_message" id="form_message" aria-required="true"></textarea>
                    </div>
                  </div>
                  <div class="col-sm-12">
                    <div class="form-group mb-0 mt-10">
                      <input name="form_botcheck" class="form-control" type="hidden" value="">
                      <button type="submit" class="btn btn-colored btn-theme-color-2 text-white btn-lg btn-block" data-loading-text="Please wait...">Submit Request</button>
                    </div>
                  </div>
                </div>
              </form>
              <!-- Application Form End-->

              <!-- Application Form Validation Start-->
              <script type="text/javascript">
                $("#reservation_form").validate({
                  submitHandler: function(form) {
                    var form_btn = $(form).find('button[type="submit"]');
                    var form_result_div = '#form-result';
                    $(form_result_div).remove();
                    form_btn.before('<div id="form-result" class="alert alert-success" role="alert" style="display: none;"></div>');
                    var form_btn_old_msg = form_btn.html();
                    form_btn.html(form_btn.prop('disabled', true).data("loading-text"));
                    $(form).ajaxSubmit({
                      dataType:  'json',
                      success: function(data) {
                        if( data.status == 'true' ) {
                          $(form).find('.form-control').val('');
                        }
                        form_btn.prop('disabled', false).html(form_btn_old_msg);
                        $(form_result_div).html(data.message).fadeIn('slow');
                        setTimeout(function(){ $(form_result_div).fadeOut('slow') }, 6000);
                      }
                    });
                  }
                });
              </script>
              <!-- Application Form Validation Start -->
              </div>
            </div>