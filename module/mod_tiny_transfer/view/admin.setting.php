<link rel="stylesheet" href="/module/mod_tiny_transfer/assets/css/admin.css">

<div class="card flex flex-center setting-info-page">
    <div class="card-body">
        <ul class="tab">
            <li class="tab-item active">
                <a><i class="iconfont icon-shezhi"></i> Transfer Setting</a>
            </li>
        </ul>
        <div class="flex flex-center">
            <div class="col-7 col-xl-12">
                <form id="form">
                    <div class="form-groups">
                        <!-- Name -->
                        <div class="form-group">
                            <label class="form-label">Title</label>
                            <input class="form-input" type="text" placeholder="Title" name="title"
                                value="<?php $this->e($v["title"]);?>">
                        </div>

                        <!-- Thumbnail -->
                        <div class="form-group">
                            <label class="form-label">Logo(200*200)</label>
                            <div class="from-upload logo-upload">
                                <input type="file">
                                <div class="uploader-input">
                                    <div class="progress-panel">
                                        <div class="file-progress-bar"></div>
                                        <span></span>
                                    </div>
                                    <?php
                                    if (isset($v["logo"])) {
                                        ?>
                                    <div class="preview" style="background-image: url(<?php $this->e($v["logo"]); ?>);">
                                        <input type="hidden" class="img" name="logo"
                                            value="<?php $this->e($v["logo"]); ?>">
                                    </div>
                                    <?php
                                    } else {?>
                                    <div class="preview">
                                        <input type="hidden" class="img" name="logo">
                                    </div>
                                    <?php }?>
                                    <span class="bf">Browse files</span>
                                </div>

                            </div>
                        </div>


                        <!-- Description -->
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea class="form-input" name="description" placeholder="Description"
                                rows="3"><?php $this->e($v["description"]);?></textarea>
                        </div>

                        <!-- Keywords -->
                        <div class="form-group">
                            <label class="form-label">Keywords</label>
                            <textarea class="form-input" name="keywords" placeholder="Keywords"
                                rows="3"><?php $this->e($v["keywords"]);?></textarea>
                        </div>
                        
                    </div>

                    <!-- hr -->
                    <div class="col-12">
                        <hr>
                    </div>
                    <!-- Submit -->
                    <div class="col-12 text-center">
                        <button type="button" class="btn btn-primary save">
                            <i class="iconfont icon-save"></i> Save Changes
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</div>

<script src="/module/mod_tiny_transfer/assets/js/admin.uploader.js"></script>
<script src="/module/mod_tiny_transfer/assets/js/admin.setting.js"></script>