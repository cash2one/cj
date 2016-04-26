//添加笔记



(function (app) {
    app.controller('AddCtrl', ['$scope', '$timeout', 'NoteApi', function ($scope, $timeout, NoteApi) {

            $scope.note_title = ""; //
            $scope.addFileList = [];
            var issaveing = true;
            //		 文本编辑框
            var opt = {"toolbars":
                        [["source", "|", "bold", "italic", "underline", "removeformat", "|",
                                "forecolor", "backcolor", "insertorderedlist", "insertunorderedlist",
                                "fontfamily", "fontsize", "|", "justifyleft", "justifycenter",
                                "justifyright", "justifyjustify", "|", "link", "unlink",
                                "insertimage", "insertvideo"]],
                "textarea": "content",
                "initialFrameHeight": 300,
                "initialContent": "",
                "elementPathEnabled": false,
                "charset": "utf-8",
                "lang": "zh-cn",
                "autoClearinitialContent": false,
                "emotionLocalization": true,
                "pageBreakTag": "ueditor_page_break_tag"};
            UE.getEditor("myEditor", opt);

            /*页面初始化*/
            (function () {
                $scope.grandPaIndex = 0; //第一层自定义下标
                $scope.parentIndex = 0;//第二层自定义下标
                $scope.childIndex = 0;//第三层自定义下标
                NoteApi.getCateList({}).then(function (data) {

                    if (data.errcode == 0) {
                        $scope.grandPaData = data.result.cates; //初始化第一层数组
                        if ($scope.grandPaData[$scope.grandPaIndex].id) {
                            $scope.parentData = $scope.grandPaData[$scope.grandPaIndex].sub;
                            if ($scope.parentData.length == 0) {
                                $scope.parentData = [];
                                $scope.childData = null;
                            } else {
                                if ($scope.parentData[$scope.parentIndex].id) {
                                    $scope.childData = $scope.parentData[$scope.parentIndex].sub;
                                } else {
                                    $scope.childData = null;
                                }
                            }
                        } else {
                            $scope.parentData = null;
                            $scope.childData = null;
                        }
                    }
                })

                addFile();
            })();

            //点击一级分类课程
            $scope.changeCurriculumData = function (index, type) {
                if (type == 1) {
                    $scope.grandPaIndex = index;
                    $scope.parentIndex = 0;
                    $scope.childIndex = 0;
                    if ($scope.grandPaData[$scope.grandPaIndex].id) {
                        $scope.parentData = $scope.grandPaData[$scope.grandPaIndex].sub;
                        if ($scope.parentData.length == 0) {
                            $scope.parentData = [];
                            $scope.childData = null;
                        } else {
                            if ($scope.parentData[$scope.parentIndex].id) {
                                $scope.childData = $scope.parentData[$scope.parentIndex].sub;
                            } else {
                                $scope.childData = null;
                            }
                        }

                    } else {
                        $scope.parentData = null;
                        $scope.childData = null;
                    }
                } else if (type == 2) {
                    $scope.parentIndex = index;
                    $scope.childIndex = 0;
                    if ($scope.parentData[$scope.parentIndex].id) {
                        $scope.childData = $scope.parentData[$scope.parentIndex].sub;
                    } else {
                        $scope.childData = null;
                    }
                } else if (type == 3) {
                    $scope.childIndex = index;
                }

            }


            $scope.sure = function () {
                if (!$scope.childData || $scope.childData.length == 0){
                    if (!$scope.childData) {
                        if ($scope.parentData.length == 0) {
                            $scope.selObj = {
                                selName:'',
                                selId: $scope.grandPaData[$scope.grandPaIndex].id,
                                selType: 0
                            }
                        } else {
                            $scope.selObj = {
                                selName: $scope.parentData[$scope.parentIndex].title,
                                selId: $scope.parentData[$scope.parentIndex].cid,
                                selType: 1
                            }
                        }
                    } else {
                        $scope.selObj = {
                            selName:'',
                            selId: $scope.parentData[$scope.parentIndex].id,
                            selType: 0
                        }
                    }
                } else {
                    $scope.selObj = {
                        selName: $scope.childData[$scope.childIndex].title,
                        selId: $scope.childData[$scope.childIndex].cid,
                        selType: 1
                    }
                }
            }

            $scope.cancel = function () {
                $scope.changeCurriculumData(0, 1);
            }

            function getOptionsIndex(id, arr) {
                for (var i = 0; i < arr.length; i++) {
                    if (arr[i].id == id) {
                        return i;
                        break;
                    }
                }
            }

            /*var name = item['name'];
             var filetype = name.substring(name.lastIndexOf('.')+1, name.length).toLowerCase();*/
            //$scope.aaaaaa = function(){
            //	alert(UE.getEditor('myEditor').getContent())
            //}
            //
            //console.log()
            //发送请求发布数据
            $scope.save = function () {
                if (issaveing) {
                    issaveing = false;
                    NoteApi.addNote({
                        cid: $scope.selObj.selId,
                        title: $scope.note_title,
                        content: UE.getEditor('myEditor').getContent(),
                        attachs: (function () {
                            var arr = [];
                            for (var i = 0; i < $scope.addFileList.length; i++) {
                                arr.push($scope.addFileList[i].id);
                            }
                            return arr.join(",");
                        })()
                    }).then(function (data) {
                        if (data.errcode == 0) {
                            window.location.href = "/admincp/office/note/list/pluginid/48/";
                        } else {
                            issaveing = true;
                        }
                    }, function () {
                        issaveing = true;
                    })
                }

            }

            $scope.del = function (id) {
                NoteApi.deleteAttach({
                    at_id: id
                }).then(function (data) {
                    if (data.errcode == 0) {
                        for (var i = 0; i < $scope.addFileList.length; i++) {
                            var item = $scope.addFileList[i];
                            if (id == item.id) {
                                $timeout(function () {
                                    $scope.addFileList.splice(i, 1);
                                })
                                break;
                            }
                        }
                    }
                })
            }

            function addFile() {
                $('#attachUpload').fileupload({
                    dataType: 'json',
                    url: '/admincp/api/attachment/upload/?file=file&is_attach=1',
                    limitMultiFileUploads: 1,
                    sequentialUploads: true,
                    change: function (e, data) {
                        for (var i = 0; i < data.files.length; i++) {
                            var file = data.files[i];
                            if (/^(image|audio)/.test(file.type)) {
                                if (file.size > 2000000) {
                                    alert(file.name + 　'文件超过大小限制');
                                    return false;
                                }
                            } else if (/^(video)/.test(file.type)) {
                                if (file.size > 10000000) {
                                    alert(file.name + 　'文件超过大小限制');
                                    return false;
                                }
                            } else if (/^(application)/.test(file.type)) {
                                if (file.size > 20000000) {
                                    alert(file.name + 　'文件超过大小限制');
                                    return false;
                                }
                            } else {
                                if (data.files[i].size > 30000000) {
                                    alert('文件超过大小限制');
                                    return false;
                                }
                            }
                        }

                    },
                    start: function (e, data) {
                        $('#attach_progress .progress-bar').css('width', '0%');
                        $('#attach_progress').show();
                    },
                    fail: function (e, data) {

                        $('#attach_progress').hide();
                    },
                    done: function (e, data) {
                        console.log(data.result);
                        if (data.result.errcode == 0) {

                            var d = data.result.result.list[0];
                            var did = data.result.result.id;
                            var fileSplit = d.name.split(".");
                            var filetype = fileSplit[fileSplit.length - 1];
                            var o = {};
                            o.id = did;
                            o.type = filetype;
                            o.name = d.name;
                            $timeout(function () {
                                $scope.addFileList.push(o);
                            })
                        }else{
                            alert(data.result.errmsg);

                        }

                        $('#attach_progress').hide();
                    },
                    progressall: function (e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        $('#attach_progress .progress-bar').css('width', progress + '%');
                    }
                });
            }

        }]);
})(angular.module('app.modules.noteadd'));
