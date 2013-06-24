(function($){
    jQuery(document).ready(function(){
        //
        var myConfig = {
            default: {
                device: "iphone5",
                layout: "single"
            },
            path: {
                root:       "./",
                imgRoot:    "include/img/",
                ajax:       "ajax.php"
            },
            devices: {
                "iphone5": {
                    template:               "iphone5",
                    width:                  282,
                    height:                 560,
                    imgs:{
                        src_overlay:            "overlay-iphone5.png",
                        src_bg:                 "base-iphone5.png",
                        src_mask:               "mask-iphone5.png"
                    },
                    layout:{
                        brockmann:{
                            top:            [0,93,186,279,372,465],
                            left:           [0,93,186],
                            class_name:     ""
                        }
                    }
                },
                "iphone4": {
                    template:               "iphone4",
                    width:                  320,
                    height:                 560,
                    imgs:{
                        src_overlay:            "overlay-iphone4.png",
                        src_bg:                 "base-iphone4.png",
                        src_mask:               "mask-iphone4.png"
                    },
                    layout:{
                        brockmann:{
                            top:            [0,112,224,336,448],
                            left:           [-8,104,216],
                            class_name:     ""
                        }
                    }
                },
                "samsung-galaxy-s3": {
                    template:               "samsung-galaxy-s3",
                    width:                  302,
                    height:                 560,
                    imgs:{
                        src_overlay:            "overlay-samsung-galaxy-s3.png",
                        src_bg:                 "base-samsung-galaxy-s3.png",
                        src_mask:               "mask-samsung-galaxy-s3.png"
                    },
                    layout:{
                        brockmann:{
                            top:            [18,84,150,216,282,348,414,480],
                            left:           [20,86,152,218],
                            class_name:     " square-grid"
                        }
                    }
                }
            }
        };
        //
        var App = App || {};
        //
        App.Header = Backbone.View.extend({
            el: ".bHeader",
            events: {
                "click .eHeader_title h1": "insertTitle",
                "blur .eHeader_title input": "saveTitle",
                "click #button-save": "saveDesign",
                "click #button-cancel": "clearDesign",
                "click #button-reset": "resetDesign"
            },
            defaultTitle: "TITLE OF YOUR CASE",
            initialize: function(){ },
            insertTitle: function(){
                var title_h1 = $(".eHeader_title h1"),
                    title_input =  $(".eHeader_title input");

                title_h1.attr("hidden",true);
                title_input.removeAttr("hidden").trigger("focus");
            },
            saveTitle: function(){
                var title_h1 = $(".eHeader_title h1"),
                    title_input =  $(".eHeader_title input");

                if( title_input.val() != "" ){
                    title_h1.text( title_input.val().replace(/[^a-zA-Zа-яА-Я0-9\-\_]{1,}/gi,"") );
                }else{
                    title_h1.text( this.defaultTitle );
                }
                title_h1.removeAttr("hidden");
                title_input.attr("hidden",true);
            },
            saveDesign: function(){
                Core.saveDesign();
                return false;
            },
            clearDesign: function(){
                Core.clearDesign();
            },
            resetDesign: function(){
                Core.clearDesign();
                return false;
            }
        });
        //
        App.Sidebar = Backbone.View.extend({
            el: ".bSideMenu",
            events: {
                "sidebar_start_load":               "startLoading",
                "sidebar_end_load":                 "endLoading",
                "click .eSideMenu_ul>li>a":         "openItem",
                "click [data-device-select-id]":    "selectDevice",
                "click .eSideMenu_subUl.layout>li": "selectLayout",
                "click .eSideMenu_subUl.case>li":   "selectCase"
            },
            tempStatus: $('<div class="bOverlay mSidebar"><img src="'+ myConfig.path.root+myConfig.path.imgRoot +'loading-large-black.gif" alt=""/></div>'),
            initialize: function(){
                this.$el.css({display: "block"});
            },
            startLoading: function(){
                this.$el.append( this.tempStatus.clone() );
            },
            endLoading: function(){
                $(".bOverlay.mSidebar", this.$el).remove();
            },
            openItem: function(event){
                if( !$(event.currentTarget).parent().hasClass("selected") ){
                    $(".eSideMenu_ul>li").removeClass("selected");
                    $(".eSideMenu_ul>li>.eSideMenu_subUl").slideUp(300);
                    $(".eSideMenu_subUl", event.currentTarget.parentNode)
                        .slideDown(300)
                        .queue(function(){
                            $(event.currentTarget).parent().addClass("selected");
                            $(this).dequeue();
                        });
                }
                //
                return false;
            },
            selectDevice: function(event){
                var node = $(event.currentTarget),
                    dev = node.data("device-select-id");
                //
                $(".eSideMenu_subUl.device>li").removeClass("selected");
                node.addClass("selected");
                // Показать прелоудер
                this.$el.trigger("sidebar_start_load");
                // Подставить выбранное устройство
                $(".eSideMenu_phoneA strong", this.$el).text( node.text() );
                // Отобразить шаблоны под выбранное устройство
                $(".eSideMenu_subUl.layout [data-device-type]", this.$el).attr("hidden", true);
                $(".eSideMenu_subUl.layout [data-device-type='"+ dev +"']", this.$el).removeAttr("hidden");
                // Отобразить чехлы под выбранное устройство
                $(".eSideMenu_subUl.case [data-device-type]", this.$el).attr("hidden", true);
                $(".eSideMenu_subUl.case [data-device-type='"+ dev +"']", this.$el).removeAttr("hidden");
                //
                Core.setDevice( dev );
            },
            selectLayout: function(event){
                var node = $(event.currentTarget);
                //
                $(".eSideMenu_subUl.layout>li").removeClass("selected");
                node.addClass("selected");
                //
                $(".eSideMenu_layoutA strong", this.$el).text( node.text() );
                //
                Core.setLayout( node.data("tpl") );
            },
            selectCase: function(event){
                var node = $(event.currentTarget);
                //
                $(".eSideMenu_subUl.case>li").removeClass("selected");
                node.addClass("selected");
                //
                $(".eSideMenu_caseColorA strong", this.$el).text( $("span", node).text() );
                //
                Core.setCase( node.data("tpl") );
            }
        });
        //
        /*App.FotosStart = Backbone.View.extend({
            el: ".bStartPanel",
            events: {

            },
            initialize: function(){

            }
        });*/
        //
        App.Fotos = Backbone.View.extend({
            el: ".bFotos",
            events: {
                "foto_start": "loaderStart",
                "foto_finish": "loaderFinish",
                "foto_upload_start": "uploadStart",
                "foto_upload_finish": "uploadFinish",
                "load_imgs": "loadImgs",
                "change #fileUploader": "sendFile",
                "resize window": "sliderCheck",
                "click .eFoto_prev": "sliderPrev",
                "click .eFoto_next": "sliderNext"
            },
            tempStatus: $('<div class="bOverlay mFoto"><img src="'+ myConfig.path.root + myConfig.path.imgRoot +'loading-large-black.gif" alt=""/></div>'),
            initialize: function(){},
            loaderStart: function(){
                this.$el.append( this.tempStatus.clone() );
            },
            loaderFinish: function(){
                $('.bOverlay.mFoto', this.$el).remove();
            },
            uploadStart: function(){
                $(".bOverlay.mUpload").removeAttr("hidden");
            },
            uploadFinish: function(){
                $(".bOverlay.mUpload").attr("hidden", true);
            },
            sendFile: function(){
                this.$el.trigger("foto_finish");
                this.$el.trigger("foto_upload_start");
                //
                Core.sendFile();
            },

            sliderCheck: function(){
                var nodes = $(".eFoto_sliderWrap, .eFoto_userPicture",".eFoto_slider"),
                    allW = 0;
                // Подсчет размера слайдов
                _.each(nodes, function(val, key, list){
                    allW += $(val).outerWidth(true);
                },this);
                // Возврат возможности прокрутки блока
                return allW > $(".eFoto_slider").outerWidth(true) - 60;
            },
            sliderPrev: function(){
                if( this.sliderCheck() ){
                    var n = $(".eFoto_slider");
                    //
                    n.animate({
                        scrollLeft: n.scrollLeft() - 88 * 5
                    },300);
                }
            },
            sliderNext: function(){
                if( this.sliderCheck() ){
                    var n = $(".eFoto_slider");
                    //
                    n.animate({
                        scrollLeft: n.scrollLeft() + 88 * 5
                    },300);
                }
            }
        });
        //
        App.Popup = Backbone.View.extend({
            el: ".bPopup",
            events: {
                "click .ePopup_linkClose": "hide"
            },
            initialize: function(){ },
            show: function(type, html){
                $('.ePopup_link_a', this.$el).attr('href',html).text(html);
                //
                this.$el.removeAttr('hidden');
            },
            hide: function(){
                this.$el.attr('hidden',true);
            }
        });
        //
        /*App.DesignPanel = Backbone.View.extend({
            el: ".bDesignPanel",
            events: {
                "device_set": "setDevice",
                "device_loaded": "isLoaded",
                "device_layout": "setLayout",
                "device_case": "setCase",
                "device_layout_is_set": "setlayoutDrop",
                "dragDrop_image_loaded": "imageLoaded"
            },
            tempStatus: $('<div class="bOverlay mPlaceholder"><img src="'+ myConfig.path.root+myConfig.path.imgRoot +'loading-large-black.gif" alt=""/></div>'),
            initialize: function(){ }
        });*/
        //
        /*App.Placeholder = Backbone.View.extend({

        });*/
        //
        var Core = function(){
            // View: initialize
            var headerApp = new App.Header;
            var sidebarApp = new App.Sidebar;
            var fotosApp = new App.Fotos;
            var PopupApp = new App.Popup;
            //
            var device = false;
            var layout = false;
            var caseType = false;
            var placeholders = [];

            var sandboxLayout = function(node){
                //
                var nodes = {
                    bg:             $('<div class="eDesignPanel_editeBackground" data-edite-div="true"></div>'),
                    transformCopy:  $('<div class="eDesignPanel_editeTransformCopy" data-edite-div="true"></div>'),
                    bgMask:         $('<div class="eDesignPanel_editeBlackMask" data-edite-div="true"></div>'),
                    transformGrid:  $('<div class="eDesignPanel_editeTransformGrid" data-edite-div="true"></div>'),
                    transformPanel: $('<div class="eDesignPanel_editeTransformTool" data-edite-div="true">'),
                    editeMove:      $('<div class="eDesignPanel_editeMove" hidden data-edite-div="true"></div>'),
                    editeResize:    $('<div class="eDesignPanel_editeResize" hidden data-edite-div="true"></div>'),
                    editeBin:       $('<div class="eDesignPanel_editeBin" data-edite-div="true"></div>'),
                    editeTick:      $('<div class="eDesignPanel_editeTick" data-edite-div="true"></div>'),

                    loadImg:        false
                };
                //
                var vars = {
                    default: {
                        width:      false,
                        height:     false,
                        top:        false,
                        left:       false,
                        scale:      false
                    }
                };
                //
                var private = {
                    init: function(){
                        node.droppable({drop: $.proxy(this.drop, this)})
                            .on({dblclick: $.proxy(this.edite, this)});
                    },
                    tempEdite: false,
                    tempStatus: $('<div class="bOverlay mPlaceholder"><img src="'+ myConfig.path.root+myConfig.path.imgRoot +'loading-large-black.gif" alt=""/></div>'),
                    drop: function(event, ui){
                        var n = $(ui.helper),
                            count = 0,
                            img1 = new Image(),
                            img2 = new Image();

                        if( !n.hasClass("img-holder") && !n.attr('data-edite-div') ){
                            // Подстановка урла изображений для загрузки
                            img1.src = n.data("standard_resolution");
                            img2.src = n.data("low_resolution");
                            // Подстановка статуса загрузки
                            $(event.target).append( this.tempStatus.clone() );
                            // Функция загрузки
                            var onLoad = $.proxy(function(){
                                if( ++count == 2 ){
                                    $('.bOverlay.mPlaceholder').remove();
                                    this.imageInsert(event, ui, img1, img2);
                                }
                            },this);
                            img1.onload = onLoad;
                            img2.onload = onLoad;
                        }
                    },
                    imageInsert: function(event, ui, imgBig, imgMed){
                        var img = $(ui.helper),
                            target = $(event.target);
                        //
                        img
                            .attr({
                                "style":        "",
                                "class":        "img-holder",
                                "data-top":     0,
                                "data-left":    0,
                                "data-scale":   1
                            });
                        target.html( img );
                        // Подставляем изображение для вставки в дизайн
                        nodes.loadImg = imgBig;
                        img.attr({src: nodes.loadImg.src });
                        // Центрируем и масштабируем изображение
                        this.imageFitToFill();
                        // подставляем базовые значения
                        img.width( vars.default.width );
                        img.height( vars.default.height );
                        img.data({
                            "top":     vars.default.top,
                            "left":    vars.default.left,
                            "scale":   vars.default.scale
                        });
                        // Устанавливаем базовые отступы
                        vars.offset = {
                            top:    img.offset().top,
                            left:   img.offset().left
                        };
                        // Отрисовываем изображение в дизайне
                        this.imageDraw(nodes.loadImg,vars.default.left,vars.default.top,vars.default.width,vars.default.height);
                    },
                    imageFitToFill: function(){
                        var img = $("img",node),
                            w = nodes.loadImg.width,
                            h = nodes.loadImg.height,
                            wP = node.width(),
                            hP = node.height(),
                            buffX = 0, buffY = 0,
                            scale = 0,
                            ratio = w / h,
                            newH = hP,
                            newW = Math.ceil(newH * ratio);
                        //
                        if( newW > wP ){
                            buffX = -Math.ceil( (newW - wP) / 2 );
                            scale = h / hP;
                        }else{
                            buffY = -Math.ceil( (newH - hP) / 2 );
                            scale = w / wP;
                        }
                        //
                        vars.default = {
                            width:      newW,
                            height:     newH,
                            left:       buffX,
                            top:        buffY,
                            scale:      scale
                        }
                    },
                    imageDraw: function(imgNode,x,y,width,height){
                        //
                        var preview = $(".eDesignPanel_preview")[0].getContext("2d"),
                            //buffer = document.createElement("canvas"),
                            //bufferContext = buffer.getContext("2d"),
                            mask = new Image();
                        // Показываем только не пересекающиеся фигуры
                        preview.globalCompositeOperation="source-out";
                        // Очищаем полотко preview
                        preview.clearRect(0,0,node.width(),node.height());
                        // Загрузка большого изображения и подгонка
                        mask.src = myConfig.path.root + myConfig.path.imgRoot + device.imgs.src_mask;
                        mask.onload = $.proxy(function() {
                            preview.drawImage(mask,0,0);
                            preview.drawImage(imgNode,x,y,width,height);
                        },this);
                    },
                    imageClear: function(){
                        // TODO: доделать
                        $(".eDesignPanel_preview")[0].getContext("2d").clearRect(0,0,device.width,device.height);
                    },
                    edite: function(event){
                        var img = $("img", node);
                        if( img.length > 0 ){
                            this.editeCreateTemplate();
                            this.editeSetImageMove();
                        }
                    },
                    editeDestroyTemplate: function(){
                        $("[data-edite-div]",".bDesignPanel").remove();
                        //
                        $(".bDesignPanel").removeClass("mEdite");
                    },
                    editeCreateTemplate: function(){
                        var parent = $(".bDesignPanel"),
                            img = $("img", node);
                        //
                        parent.addClass("mEdite");
                        //
                        var nodeMove = nodes.editeMove.clone();
                        var nodeResize = nodes.editeResize.clone();
                        var nodeBin = nodes.editeBin.clone();
                        var nodeTick = nodes.editeTick.clone();
                        var nodePanel = nodes.transformPanel.clone()
                            .css({
                                width:      img.width() + 16,
                                height:     img.height() + 16,
                                top:        img.data("top") - 8,
                                left:       img.data("left") - 8
                            })
                            .append( nodeMove )
                            .append( nodeResize )
                            .append( nodeBin )
                            .append( nodeTick );
                        var nodeGrig = nodes.transformGrid.clone()
                            .css({
                                width:  node.width(),
                                height: node.height()
                            })
                            .append(
                                img.clone().css({ top: img.data("top"), left: img.data("left") })
                            );
                        var nodeTools = nodes.transformCopy.clone()
                            .css({
                                width:  node.width(),
                                height: node.height()
                            })
                            .append(
                                img.clone().css({top: img.data("top"), left: img.data("left")})
                            );
                        // Добавляем ноды
                        parent
                            .prepend( nodePanel )
                            .prepend( nodeGrig )
                            .prepend( nodes.bgMask.clone() )
                            .prepend( nodeTools )
                            .prepend( nodes.bg.clone() );
                        // Вешаем события
                        nodeMove.on("click",    $.proxy(this.editeMove,this));
                        //nodeResize.on("click",  $.proxy(this.editeResize,this));
                        nodeBin.on("click",     $.proxy(this.editeBin,this));
                        nodeTick.on("click",    $.proxy(this.editeTick,this));
                        // drag n drop
                        nodeResize.draggable({
                            helper:     "clone",
                            cursor:     "ne-resize",
                            drag:       $.proxy(this.editeResize,this),
                            start:      function(e, ui){
                                $(ui.helper).attr("hidden", true);
                            }
                        });
                        /*nodeMove.draggable({
                            helper:     "clone",
                            cursor:     "move",
                            drag:      $.proxy(function(e,ui){
                                $(e.target).css({opacity: 0});

                                var pos = {
                                    top: ui.position.top + 8,
                                    left: ui.position.left + 8
                                };

                                this.editeDrag(pos, $(".bDesignPanel .eDesignPanel_editeTransformGrid>img"));
                            }, this)
                        });*/
                    },
                    editeMoveTemplate: function(){
                        var nodeTools = $('.eDesignPanel_editeTransformCopy'),
                            nodeGrig = $('.eDesignPanel_editeTransformGrid'),
                            nodePanel = $('.eDesignPanel_editeTransformTool'),
                            img1 = $("img",nodeTools),
                            img2 = $("img",nodeGrig);

                        nodePanel.css({
                            width:      img1.width() + 16,
                            height:     img1.height() + 16,
                            top:        img1.data("top") - 8,
                            left:       img1.data("left") - 8
                        })
                    },
                    editeSetImageMove: function(){
                        var img = $(".bDesignPanel .eDesignPanel_editeTransformGrid>img");

                        img.draggable({
                            helper:     "original",
                            scroll:     false,
                            cursor:     "move",
                            drag:      $.proxy(function(e,ui){
                                this.editeDrag(ui.position, img);
                            }, this)
                        });
                    },
                    editeMove: function(event){
                        console.log("editeMove",event)
                    },
                    editeResize: function(event, ui){
                        var img = $("img",node),
                            newTop = ui.offset.top,
                            parentTop = vars.offset.top + 8,
                            difference = parentTop - newTop,
                            minTop = img.offset().top - 8,
                            maxTop = $('.bHeader').height();

                        var img1 = $(".eDesignPanel_editeTransformCopy img"),
                            img2 = $(".eDesignPanel_editeTransformGrid img"),
                            oldW = img.width(),
                            oldH = img.height(),
                            oldLeft = parseInt(img.data('left')),
                            oldTop = parseInt(img.data('top')),
                            _css = {
                                top:        oldTop - difference,
                                left:       oldLeft - difference,
                                width:      oldW + difference * 2,
                                height:     oldH + difference * 2
                            };


                        if( _css.width <= node.width() || _css.height <= node.height() ){
                            _css = {
                                top:        oldTop - difference,
                                left:       oldLeft - difference,
                                width:      vars.default.width,
                                height:     vars.default.height
                            }
                        }

                        /*console.log(
                         'old: '+ oldW +'x'+ oldH,
                         ' new: '+ (oldW + addToTop * 2 ) +'x'+ ( oldH + addToTop * 2),
                         ' oldPos: '+ oldTop +','+ oldLeft,
                         ' newPos:'+ (oldTop + addToTop) +','+ (oldLeft + addToTop)
                         )*/
                        console.log(
                            'top: '+ _css.top,
                            'left: '+ _css.left,
                            'width: '+ _css.width,
                            'height:'+ _css.height
                        )
                        //
                        img1.css(_css);
                        img2.css(_css);
                        //
                        img1.data('top', _css.top);
                        img1.data('left', _css.left);
                        img2.data('top', _css.top);
                        img2.data('left', _css.left);
                        //
                        this.editeMoveTemplate();
                    },
                    editeResizePosition: function(addToTop){
                        var img = $("img",node),
                            img1 = $(".eDesignPanel_editeTransformCopy img"),
                            img2 = $(".eDesignPanel_editeTransformGrid img"),
                            oldW = img.width(),
                            oldH = img.height(),
                            oldLeft = parseInt(img.data('left')),
                            oldTop = parseInt(img.data('top')),
                            _css = {
                                top:        oldTop - addToTop,
                                left:       oldLeft - addToTop,
                                width:      oldW + addToTop * 2,
                                height:     oldH + addToTop * 2
                            };

                        /*console.log(
                            'old: '+ oldW +'x'+ oldH,
                            ' new: '+ (oldW + addToTop * 2 ) +'x'+ ( oldH + addToTop * 2),
                            ' oldPos: '+ oldTop +','+ oldLeft,
                            ' newPos:'+ (oldTop + addToTop) +','+ (oldLeft + addToTop)
                        )*/
                        //
                        img1.css(_css);
                        img2.css(_css);
                        //
                        img1.data('top', _css.top);
                        img1.data('left', _css.left);
                        img2.data('top', _css.top);
                        img2.data('left', _css.left);
                        //
                        this.editeMoveTemplate();
                    },
                    editeBin: function(event){
                        // TODO: доделать
                        this.imageClear();
                        this.editeDestroyTemplate();
                        $(">img", node).remove();
                    },
                    editeTick: function(event){
                        // TODO: доделать
                        var img = $(".bDesignPanel .eDesignPanel_editeTransformGrid>img"),
                            parentImg = $("img", node),
                            top = parseInt(img.css("top")),
                            left = parseInt(img.css("left"));
                        //
                        parentImg.data("top", top );
                        parentImg.data("left", left );
                        //
                        this.imagePosition( left, top );
                        //
                        this.editeDestroyTemplate();
                        //console.log("editeTick",event)
                    },
                    editeDrag: function(pos, img){
                        var top = 0,
                            left = 0;

                        // top
                        if( Math.abs(pos.top) + node.height() < img.height() ){
                            top = pos.top;
                        }else{
                            top = pos.top = node.height() - img.height();
                        }
                        // left
                        if( pos.left < 0 ){
                            left = pos.left;
                        }else{
                            pos.left = 0;
                        }
                        if( Math.abs(pos.left) + node.width() < img.width() ){
                            left = pos.left;
                        }else{
                            left = pos.left = node.width() - img.width();
                        }

                        $(".bDesignPanel .eDesignPanel_editeTransformCopy>img").css({
                            top:    top,
                            left:   left
                        });
                        $(".bDesignPanel .eDesignPanel_editeTransformTool").css({
                            top:    top - 8,
                            left:   left - 8
                        });
                    }
                };
                //
                var public = {
                    getData: function(){
                        var img = $("img", node);
                        return {
                            id:     img.data("id"),
                            top:    img.data("top"),
                            left:   img.data("left"),
                            scale:  img.data("scale"),
                            width:  node.width(),
                            height: node.height()
                        }
                    },
                    clearData: function(){
                        private.editeBin();
                    }
                };
                //
                private.init();
                //
                return {
                    getData:        public.getData,
                    clearData:      public.clearData
                }
            };
            //
            var private = {
                ajax: function(data, callback ,type){
                    $.ajax({
                        url:        myConfig.path.root + myConfig.path.ajax,
                        type:       type||"GET",
                        dataType:   "json",
                        timeout:    60000,
                        data:       data,
                        success:    callback,
                        error:      $.proxy(this.ajaxError, this)
                    });
                },
                ajaxError: function(jqXHR, textStatus, errorThrown){
                    //
                    fotosApp.$el.trigger("foto_finish");
                    //
                    console.log( "ajaxError", jqXHR, textStatus, errorThrown );
                },
                bigPreloaderStart: function(){
                    $(".bOverlay.mSaving").removeAttr("hidden");
                },
                bigPreloaderFinish: function(){
                    $(".bOverlay.mSaving").attr("hidden", true);
                },

                createPreview: function(obj){
                    var span = $('<span></span>',{
                        "id":                           "img-"+obj.id,
                        "class":                        "eFoto_userPicture"
                    });
                    var img = $('<img/>',{
                        "src":                          obj.img_path_150,
                        "class":                        "grabbable ",
                        "alt":                          "",
                        "data-thumbnail":               obj.img_path_150,
                        "data-low_resolution":          obj.img_path_400,
                        "data-standard_resolution":     obj.img_path_2560,
                        //"draggable": true,
                        "data-draggable": true,
                        "data-url": "",
                        "data-id":                      obj.id
                    });
                    var del = $('<span class="eFoto_userPictureDelete" id="delete-'+ obj.id +'" data-id="">x</span>');
                    del.on("click", $.proxy(this.removeImg,this));
                    //
                    img.css({opacity: 0});
                    img.load(function(){
                        img.animate({opacity: 1},300);
                    });
                    //
                    span.append( img ).append( del );
                    //
                    return {span: span, img: img, del: del};
                },
                successImgs: function(data, textStatus, jqXHR){
                    for( var i in data ){
                        var obj = this.createPreview(data[i]);
                        //
                        $(".eFoto_slider").append( obj.span );
                    }
                    // Убираем статус работы
                    fotosApp.$el.trigger("foto_finish");
                    this.setImgDrag();
                },
                iFrameLoad: function(){
                    //  Открепляем событие
                    $("#client_proxy").off("load");
                    // Делаем запрос на подучения последней загруженной фотки
                    this.ajax(
                        {last_upload_img: "1"},
                        $.proxy(this.successUploadImg, this)
                    );
                },
                successUploadImg: function(data, textStatus, jqXHR){
                    var obj = this.createPreview( data );
                    // Добавляем ноды новой фотки
                    $(".eFoto_sliderWrap").after( obj.span );
                    // Убираем статус работы
                    fotosApp.$el.trigger("foto_finish");
                    fotosApp.$el.trigger("foto_upload_finish");
                    this.setImgDrag();
                },
                removeImg: function(event){
                    fotosApp.trigger("foto_start");
                    //
                    this.ajax(
                        {action: "remove_img", img_id: $(event.delegateTarget).attr("id").replace("delete-","") },
                        $.proxy(function(data, textStatus, jqXHR){
                            $(".eFoto_userPicture").remove();
                            this.successImgs(data, textStatus, jqXHR);
                        }, this)
                    );
                },

                setImgDrag: function(){
                    $(".eFoto_userPicture>img").draggable({
                        disabled:   false,
                        helper:     "clone",
                        stack:      ".draggable .draggable-img",
                        cursor:     "move",
                        appendTo:   $("body"),
                        start:      $.proxy(this.dragStart, this),
                        stop:       $.proxy(this.dragStop, this)
                    });
                },
                dragStart: function(){
                    $(".eDesignPanel_templateWrap").addClass("js_over");
                },
                dragStop: function(){
                    $(".eDesignPanel_templateWrap").removeClass("js_over");
                },

                resizing: function(){
                    // Позиционирование телефона
                    var node = $(".bDesignPanel"),
                        winH = $(window).height(),
                        phoneH = device.height;
                    //
                    if( winH > node.height() ){
                        var h = (winH - phoneH - 115 - 36)/2;
                        node.css({ top: h>60? h : 60 })
                    }else{
                        node.css({ top: 60 });
                    }
                    //
                    var nodeD = $(".bDesignPanel"),
                        winH = parseInt($(window).height()),
                        menuH = parseInt(nodeD.outerHeight(true)) + parseInt(nodeD.css("top")),
                        devH = device.height + parseInt(nodeD.css("top")),
                        contentH = winH - 115 - 36;
                    // Ваставление высоты блока с основным контентом
                    $(".bContent").height( contentH );
                    // Выставление высоты меню
                    if( contentH < devH ){
                        $(".bSideMenu").height( menuH + 30 );
                    }else{
                        $(".bSideMenu").height( "100%" );
                    }
                },
                img: false,
                loadDevice: function(){
                    $(window).off("resize");
                    // Записываем количество фотографий для загрузки
                    this.img = {
                        all:    _.size( device.imgs ),
                        done:   0
                    };
                    // Загрузка всех изображений устройства
                    _.each(device.imgs, function(val,key){
                        if( typeof val == "string" ){
                            var pic = new Image();
                            pic.src = myConfig.path.root + myConfig.path.imgRoot+val;
                            $(pic).load($.proxy(this.setIsLoad,this));
                        }
                    },this);
                },
                setIsLoad: function(){
                    this.img.done++;
                    if( this.img.all == this.img.done ){
                        this.isLoaded();
                    }
                },
                isLoaded: function(){
                    this.img = false;
                    // Подстановка размеров холста для canvas
                    $(".eDesignPanel_overlay, .eDesignPanel_preview, .eDesignPanel_bg, .eDesignPanel_mask").attr({
                        width:  device.width,
                        height: device.height
                    });
                    // Подстановка изображений в canvas
                    var arr = {
                        eDesignPanel_overlay:   device.imgs.src_overlay,
                        eDesignPanel_bg:        device.imgs.src_bg,
                        eDesignPanel_mask:      device.imgs.src_mask
                    };
                    //
                    _.each(arr, function(val,key){
                        var n = $("."+key)[0],
                            canvas = n.getContext("2d");

                        var pic = new Image();
                        pic.src = myConfig.path.root + myConfig.path.imgRoot+val;
                        pic.onload = function() {
                            canvas.drawImage(pic, 0, 0);
                        };
                    }, this);
                    //
                    sidebarApp.$el.trigger("sidebar_end_load");
                    //
                    $(window).on("resize",$.proxy(this.resizing,this)).trigger("resize");
                    //
                    $(".eSideMenu_subUl.layout>li[data-device-type='"+ device.template +"']:eq(0)").trigger("click");
                    $(".eSideMenu_subUl.case>li[data-device-type='"+ device.template +"']:eq(0)").trigger("click");
                    //public.setLayout( myConfig.default.layout );
                },

                createLayout: function(type){
                    placeholders = [];
                    switch(type){
                        case "single":
                            //
                            var node = $('<div class="placeholder grabbable single-grid" data-tpid="0"></div>').css({
                                zIndex:     60,
                                top:        0,
                                left:       0,
                                width:      device.width,
                                height:     device.height
                            });
                            var node2 = $('<div class="normal-grid"></div>').css({
                                top:        0,
                                left:       0,
                                width:      device.width,
                                height:     device.height
                            });
                            //
                            $(".eDesignPanel_templateWrap").html("").append('<div id="canvas-loader"></div>').append( node ).append( node2 );
                            //
                            placeholders.push( new sandboxLayout(node) );
                            break;
                    }
                },

                setCase: function(){
                    console.log( caseType );
                },

                saveComplete: function(data, textStatus, jqXHR){
                    //console.log( "saveComplete", data );
                    this.bigPreloaderFinish();
                    //
                    public.clearDesign();
                    //
                    PopupApp.show('link',data.save);
                }
            };
            //
            var public = {
                init: function(){
                    // Загрузка фоток
                    fotosApp.$el.css({display: "block"}).trigger("foto_start");
                    private.ajax(
                        {action: "get_imgs"},
                        $.proxy(private.successImgs, private)
                    );
                    //
                    $(".eSideMenu_ul>li:eq(0)>a").trigger("click");
                    $(".eSideMenu_ul>li:eq(0)>.eSideMenu_subUl>li:eq(0)").trigger("click");
                },
                setDevice: function(name){
                    if( myConfig.devices[name] ){
                        device = myConfig.devices[name];
                        private.loadDevice();
                    }
                },
                setLayout: function(name){
                    layout = name;
                    //
                    private.createLayout(name);
                },
                setCase: function(name){
                    caseType = name;
                    //
                    private.setCase();
                },
                sendFile: function(name){
                    $("#client_proxy").on("load", $.proxy(private.iFrameLoad, private));
                    $("#photo-upload-form").submit();
                },
                saveDesign: function(){
                    //
                    var temp = [];
                    for( var i = 0, c = placeholders.length; i < c; i++ ){
                        console.log( placeholders[i].getData() );
                        temp.push( placeholders[i].getData() );
                    }
                    //
                    private.bigPreloaderStart();
                    //
                    private.ajax(
                        {
                            save_design: "true",
                            placeholders: temp,
                            device: device,
                            layout: layout,
                            caseType: caseType,
                            title: $(".eHeader_title h1").text()
                        },
                        $.proxy(private.saveComplete, private),
                        "POST"
                    );
                },
                clearDesign: function(){
                    // TODO: доделать
                    // Обнуляем название
                    $(".eHeader_title input").val("").trigger("blur");
                    // Чистим ячейку
                    placeholders[0].clearData();
                }
            };
            //
            return {
                init:           public.init,
                setDevice:      public.setDevice,
                setLayout:      public.setLayout,
                setCase:        public.setCase,
                sendFile:       public.sendFile,
                saveDesign:     public.saveDesign,
                clearDesign:    public.clearDesign
            }
        }();
        //
        Core.init();

    });
})(jQuery)