/**
 * Created by Chan on 2016/4/13.
 */
(function(app) {
    app.controller("newPrizeCtrl", ["$scope", "ScoreApi", "PersonChooser","$location", function($scope, ScoreApi, PersonChooser,$location) {
        // alert(PersonChooser);
        var id = $location.search().id;
        var editor;
        var editorOption = {
            "toolbars": [
                ["fullscreen", "source", "|", "bold", "italic", "underline", "removeformat", "|",
                    "forecolor", "backcolor", "insertorderedlist", "insertunorderedlist", "fontfamily", "fontsize", "|",
                    "justifyleft", "justifycenter", "justifyright", "justifyjustify", "|", "inserttable", "deletetable",
                    "insertparagraphbeforetable", "insertrow", "deleterow", "insertcol", "deletecol", "mergecells",
                    "mergeright", "mergedown", "splittocells", "splittorows", "splittocols", "charts", "|", "link",
                    "unlink", "insertimage", "insertvideo"
                ]
            ],
            "textarea": "content",
            "initialFrameHeight": 300,
            "initialContent": "",
            "elementPathEnabled": false,
            "serverUrl": "/admincp/ueditor/",
            "charset": "utf-8",
            "lang": "zh-cn",
            "autoClearinitialContent": false,
            "emotionLocalization": true,
            "pageBreakTag": "ueditor_page_break_tag"
        };
        $scope.saveing = false;
        if(typeof id=="undefined"){
            $scope.limit = 0;
            $scope.limitNum = null;
            $scope.uploadImgs = [];
            $scope.loadImg = [];
            $scope.choosedDepIds = [];
            $scope.m_uid = [];
            editor = UE.getEditor("myEditor", editorOption);
            $scope.$on('$destroy', function() {
                editor.destroy();
            });
        }else{
            ScoreApi.awardEdit({award_id:id}).then(function(data){
                var res = data.result.detail;
                $scope.formData.title = res.title;
                $scope.formData.stock = res.stock;
                $scope.limit = res.limit == "0" ? 0 : 1;
                $scope.limitNum = $scope.limit > 0 ? Number(res.limit) : null;
                $scope.formData.score = Number(res.score);
                $scope.uploadImgs = res.award_pic==null ? [] : res.award_pic;
                $scope.loadImg = res.pic_urls;
                $scope.m_uid = res.uids;
                (function(){
                    var arr = [];
                    for(var i=0; i<$scope.m_uid.length; i++){
                        arr.push($scope.m_uid[i].m_uid);
                    }
                    $scope.formData.uids = arr.join(',');
                })();
                editor = UE.getEditor("myEditor", editorOption);
                $scope.$on('$destroy', function() {
                    editor.destroy();
                });
                editor.ready(function(){
                    editor.setContent(res.desc, true);
               });
            });
        }

        $scope.formData = {
            title: null,
            stock:null,
            limit: null,
            score: null,
            uids: null,
            cd_ids: null,
            award_pic: null,
            desc: null
        };

        $scope.selectedMuidCallBack = function(data) {
            var select_m_uid_name = "";
            var choosePersonIds = "";

            $scope.initisshow = true;

            $scope.m_uid = data;

            for (var i = 0; i < data.length; i++) {
                if(i==data.length-1){
                    select_m_uid_name += data[i]['m_username'];
                    choosePersonIds += data[i]['m_uid'];
                }else{
                    select_m_uid_name += data[i]['m_username'] + ' ';
                    choosePersonIds += data[i]['m_uid'] + ",";
                }
            }

            if (select_m_uid_name !== '') {
                $('#m_uid_deafult_data').html(select_m_uid_name).show();
            } else {
                $('#m_uid_deafult_data').hide();
            }

            $scope.formData.uids = choosePersonIds;
        };

        /*编辑器 end*/
        $scope.save = function() {
            if(!$scope.saveing){
                $scope.saveing = true;
                setformData();

                if (!isValidForm()) {
                    $scope.saveing = false;
                    return;
                }

                if(typeof id=="undefined"){
                    ScoreApi.addAward($scope.formData).then(function(data) {
                        var result = data.result;
                        if (data.errcode === 0) {
                            /*bootbox.alert("新增成功!");
                            $scope.saveing = false;*/
                            $scope.saveing = false;
                            location.href="#/app/page/score/match_arrangement";
                        }else{
                            bootbox.alert(data.errmsg);
                            $scope.saveing = false;
                        }
                    }, function() {
                        bootbox.alert("新增失败!");
                        $scope.saveing = false;
                    });
                }else{
                    $scope.formData.award_id = id;
                    ScoreApi.editAward($scope.formData).then(function(data) {
                        var result = data.result;
                        if (data.errcode === 0) {
                            /*bootbox.alert("修改成功!");
                            $scope.saveing = false;*/
                            $scope.saveing = false;
                            location.href="#/app/page/score/match_arrangement";
                        }else{
                            bootbox.alert(data.errmsg);
                            $scope.saveing = false;
                        }
                    }, function() {
                        bootbox.alert("修改失败!");
                        $scope.saveing = false;
                    });
                }
                
            }
            
        };

        function setformData() {
            $scope.formData.limit = getLimit();
            $scope.formData.award_pic = $scope.uploadImgs;
            $scope.formData.desc = editor.getContent();
        }

        function getLimit() {
            if ($scope.limit === 0 || $scope.limit === "0") {
                return 0;
            }

            return $scope.limitNum;
        }

        function isValidForm() {
            var choosePerson = $scope.formData.cd_ids || $scope.formData.uids;

            if (choosePerson === null) {
                bootbox.alert("请选择兑换范围!");

                return false;
            }

            for (key in $scope.formData) {
                if (key === "cd_ids" || key === "uids") {
                    continue;
                }

                if ($scope.formData[key] === null) {
                    bootbox.alert("请正确填写表单内容!");

                    return false;
                }
            }

            return true;
        }
    }]);
})(angular.module("app.modules.setUp"));
