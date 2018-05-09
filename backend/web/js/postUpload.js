/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

var G_file_type = '';

$(function(){
    
  $("#uploadbtn").on('click', function() {
      $(".js-key-show #file_upload").trigger('click');
  });
  $(".js-key-show").on('change','#file_upload', function() {
    var max = 9;
    var len = $(".add-pic-list img").length;
    if((len + this.files.length) <= max){
      for(var i=0;i<this.files.length;i++){
        var csrfToken = $('#_csrf').val();
        var file = this.files[i];
        var maxsize = 1024*100;
        if (!/\/(?:jpeg|png|gif)/i.test(file.type)) {
          alert('文件格式错误');
          return false;
        }
        if (/\/(?:gif)/i.test(file.type)) {
          var reader = new FileReader();
          reader.readAsDataURL(document.querySelector("input[type=file]").files[i]);
          reader.onload = function(e) {
            $.post(
              "/upload/base-upload-img",
              {'base64':e.target.result, 'type':'base64', _csrf:csrfToken},
              function(data) {
                upSuccess(data);
              },
              'json'
            );
          }
        } else {
          G_file_type = file.type;
          var u = navigator.userAgent, app = navigator.appVersion;
          var isAndroid = u.indexOf('Android') > -1 || u.indexOf('Linux') > -1; //android终端或者uc浏览器
          var isiOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/); //ios终端
          if (isiOS) {
            var orientation = 1;
            EXIF.getData(file,function(){
              orientation = EXIF.getTag(this,'Orientation');
            });
            var reader = new FileReader();
            reader.onload = function(e) {
              getImgData(this.result,orientation,function(data){
                //这里可以使用校正后的图片data了
                var base64data = data;
                  $.post(
                    "/upload/base-upload-img",
                    {'base64':base64data, 'type':'base64', _csrf:csrfToken},
                    function(data) {
                      upSuccess(data);
                    },
                    'json'
                  );
              });
            }
            reader.readAsDataURL(file);
          } else {
            var reader = new FileReader();
            reader.onload = function () {
              var result = this.result;
              var img = new Image();
              img.src = result;
              // 图片加载完毕之后进行压缩，然后上传
              if (img.complete) {
                callback();
              } else {
                img.onload = callback;
              }
              function callback() {
                var base64data = compress(img);
                $.post(
                  "/upload/base-upload-img",
                  {'base64':base64data, 'type':'base64', _csrf:csrfToken},
                  function(data) {
                    upSuccess(data);
                  },
                  'json'
                );
              }
            };
            reader.readAsDataURL(file);
          }
        }
      }
    } else {
        alert("最多只能传"+max+"张图片");
        return false;
    }
  });


  function compress1(img) {

    var canvas = document.createElement('canvas');
    var ctx = canvas.getContext("2d");

    var width = img.width * 0.8;
    var height = img.height * 0.7;

    canvas.width =  width;
    canvas.height = height;
    //ctx.drawImage(img, 0, 0);
    ctx.clearRect(0, 0, width, height); // canvas清屏
    //重置canvans宽高 canvas.width = img.width; canvas.height = img.height;
    ctx.drawImage(img, 0, 0, width, height); // 将图像绘制到canvas上

    //onCompress(canvas.toDataURL("image/jpeg"));//必须等压缩完才读取canvas值，否则canvas内容是黑帆布

    var ndata = canvas.toDataURL('image/jpeg', 0.8);
    return ndata;
  }


  function compress(img) {
    var canvas = document.createElement("canvas");
    var ctx = canvas.getContext('2d');
    var tCanvas = document.createElement("canvas");
    var tctx = tCanvas.getContext("2d");

    var initSize = img.src.length;
    var width = img.width;
    var height = img.height;
    //如果图片大于四百万像素，计算压缩比并将大小压至400万以下
    var ratio;
    if ((ratio = width * height / 2000000)>1) {
        ratio = Math.sqrt(ratio);
        ratio = 0.9;
        width /= ratio;
        height /= ratio;
    } else {
        ratio = 1;
    }
    canvas.width = width;
    canvas.height = height;
    // 铺底色
    ctx.fillStyle = "#fff";
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    //如果图片像素大于100万则使用瓦片绘制
    var count;
    if ((count = width * height / 1000000) > 1) {
        count = 0.9;
        count = ~~(Math.sqrt(count)+1); //计算要分成多少块瓦片
        // 计算每块瓦片的宽和高
        var nw = ~~(width / count);
        var nh = ~~(height / count);
        tCanvas.width = nw;
        tCanvas.height = nh;
        for (var i = 0; i < count; i++) {
            for (var j = 0; j < count; j++) {
                tctx.drawImage(img, i * nw * ratio, j * nh * ratio, nw * ratio, nh * ratio, 0, 0, nw, nh);
                ctx.drawImage(tCanvas, i * nw, j * nh, nw, nh);
            }
        }
    } else {
        ctx.drawImage(img, 0, 0, width, height);
    }
    console.log(ctx);
    //进行最小压缩
    var ndata = canvas.toDataURL('image/jpeg', 0.8);
    //console.log('压缩前：' + initSize);
    //console.log('压缩后：' + ndata.length);
    //console.log('压缩率：' + ~~(100 * (initSize - ndata.length) / initSize) + "%");
    tCanvas.width = tCanvas.height = canvas.width = canvas.height = 0;
    return ndata;
  }

  function upSuccess(data) {
    if(data.filename){
      var str = $(".add-pic-list li:last").attr('id');
      var len = 0;
      if(str){
        len = str.substring(11)*1+1;
      }
      var id = 'upload_img_'+len;
      var img_obj='<li class="img_file_li" id="'+id+'"><a><input type="text" id="post-views_num" class="form-control" name="PostCreate[pic_list][]" value="'+data.filename+'" style="display:none;"><img src="'+showUrl+data.filename+'" data="'+showUrl+data.filename+'" style="width:100px;height:100px;" alt="" class="images" id="image'+len+'" data-key ="'+len+'" data-toggle="modal" data-target="#file"><i class="icon-close" onclick=removeImg($("#'+id+'"))></i></a></li>';
      $("#file_upload_ul").append(img_obj);
    } else {
      alert(data.msg);
    }
  }

  function ajaxFileUpload() {
    var csrfToken = $('#_csrf').val();
    $.ajaxFileUpload(
      {
        url: '/upload/upload-img', //用于文件上传的服务器端请求地址
        secureuri: false, //是否需要安全协议，一般设置为false
        fileElementId: 'file_upload', //文件上传域的ID
        dataType: 'json', //返回值类型 一般设置为json
        data:{_csrf:csrfToken},
        success: function (data, status)  //服务器成功响应处理函数
        {
          upSuccess(data);
        },
        error: function (data, status, e)//服务器响应失败处理函数
        {
            alert('上传失败，请重试！');
        }
      }
    )
    return false;
  }

  // $(".add-pic-list .icon-close").live('click',function(){
  //     $(this).closest('li').remove();
  // });

  // @param {string} img 图片的base64
  // @param {int} dir exif获取的方向信息
  // @param {function} next 回调方法，返回校正方向后的base64
  function getImgData(img,dir,next){
    var image = new Image();
    image.onload = function() {
      var degree = 0,drawWidth,drawHeight,width,height;
      drawWidth = this.naturalWidth;
      drawHeight = this.naturalHeight;
      //以下改变一下图片大小
      var maxSide = Math.max(drawWidth, drawHeight);
      if (maxSide > 1024) {
        var minSide = Math.min(drawWidth, drawHeight);
        minSide = minSide / maxSide * 1024;
        maxSide = 1024;
        if (drawWidth > drawHeight) {
          drawWidth = maxSide;
          drawHeight = minSide;
        } else {
          drawWidth = minSide;
          drawHeight = maxSide;
        }
      }
      var canvas = document.createElement('canvas');
      canvas.width = width = drawWidth;
      canvas.height = height = drawHeight;
      var context = canvas.getContext('2d');
      //判断图片方向，重置canvas大小，确定旋转角度，iphone默认的是home键在右方的横屏拍摄方式
      switch(dir){
      //iphone横屏拍摄，此时home键在左侧
      case 3:
      degree = 180;
      drawWidth =- width;
      drawHeight =- height;
      break;
      //iphone竖屏拍摄，此时home键在下方(正常拿手机的方向)
      case 6:
      canvas.width = height;
      canvas.height = width;
      degree = 90;
      drawWidth = width;
      drawHeight =- height;
      break;
      //iphone竖屏拍摄，此时home键在上方
      case 8:
      canvas.width = height;
      canvas.height = width;
      degree = 270;
      drawWidth =- width;
      drawHeight = height;
      break;
      }
      //使用canvas旋转校正
      context.rotate(degree*Math.PI/180);
      context.drawImage(this,0,0,drawWidth,drawHeight);
      //返回校正图片
      next(canvas.toDataURL("image/jpeg", 0.8));
    }
    image.src = img;
  }
  
    function removeImg(obj){
        obj.remove();
    }
  
    $('.btn-success').click(function(){
        var imgList =  $('.img_file_li');
//        var image = Array();
//        imgList.each(function(index){
//            image[index+''] = $(this).find('img').attr("data");
//        });
        if (imgList.length > 9) {
            alert('图片最多只能上传9张');
            return false;
        }
//        $('#post-pic_list').val(JSON.stringify(image));
        return true;
    });
})

