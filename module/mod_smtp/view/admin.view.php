<div class="card flex flex-center">
    <div class="card-body">
        <ul class="tab">
            <li class="tab-item active">
                <a><i class="iconfont icon-shezhi"></i> SMTP Settings</a>
            </li>
        </ul>
        <div class="flex flex-center">
            <div class="col-7 col-xl-12">
                <form id="modEmailForm">
                    <fieldset class="columns">
                        <div class="column col-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label">Smtp Host</label>
                                <input id="smtp_host" type="text" class="form-input" placeholder="smtp.gmail.com" value="<?php echo $data["smtp_host"]; ?>">
                            </div>
                        </div>
                        <div class="column col-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label">Smtp Port</label>
                                <input id="smtp_port" type="text" class="form-input" placeholder="587" value="<?php echo $data["smtp_port"]; ?>">
                            </div>
                        </div>
                        <div class="column col-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label">Auth Addr</label>
                                <input id="auth_addr" type="text" class="form-input" placeholder="email@gamil.com" value="<?php echo $data["auth_addr"]; ?>">
                            </div>
                        </div>
                        <div class="column col-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label">Auth Pass</label>
                                <input id="auth_pass" type="password" class="form-input" placeholder="******" value="<?php echo $data["auth_pass"]; ?>">
                            </div>
                        </div>
                    </fieldset>

                    <fieldset class="columns">
                        <div class="column col-12">
                            <legend>Sender settings</legend>
                        </div>
                        <div class="column col-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label">From Name</label>
                                <input id="from_name" type="text" class="form-input" placeholder="BBFPL" value="<?php echo $data["from_name"]; ?>">
                            </div>
                        </div>
                        <div class="column col-6 col-xs-12">
                            <div class="form-group">
                                <label class="form-label">From Addr</label>
                                <input id="from_addr" type="text" class="form-input" placeholder="email@gamil.com" value="<?php echo $data["from_addr"]; ?>">
                            </div>
                        </div>
                    </fieldset>

                    <div class="col-12"><hr></div>

                    <div class="column col-12">
                        <!-- Submit -->
                        <div class="text-center">
                            <button type="button" class="btn btn-primary update-btn">
                            <i class="iconfont icon-save"></i> Save Changes
                            </button>
                        </div>
                    </div>

                    <div class="col-12"><hr></div>
                </form>

                <legend><a class="btn test-settings-toggle-btn">Test your settings</a></legend>
                <fieldset class="columns test-settings" style="display:none;">
                    <div class="column col-12">
                        <div class="form-group">
                            <label class="form-label">To Addr</label>
                            <input id="test_to_addr" type="text" class="form-input" placeholder="to_email@gamil.com" value="">
                        </div>
                    </div>
                    <div class="column col-12">
                        <div class="form-group">
                            <label class="form-label">Subject</label>
                            <input id="test_subject" type="text" class="form-input" value="Test">
                        </div>
                    </div>
                    <div class="column col-12">
                        <div class="form-group">
                            <label class="form-label">Body</label>
                            <textarea class="form-input" id="test_body" rows="3">Hello</textarea>
                        </div>
                    </div>
                    <div class="column col-12 py-2">
                        <div class="text-center">
                            <button type="button" class="btn btn-success test-send-btn">
                                Send Test
                            </button>
                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
    </div>
</div>


<script src="/module/mod_smtp/assets/js/admin.main.js"></script>