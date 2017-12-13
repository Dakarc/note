<?php

namespace App\Http\Controllers\home;

use Illuminate\Http\Request;
use DB;
use Mail;
use Hash;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class TraditionController extends Controller{
    // 类别名师和视频
    public function postType(Request $request)
    {
      $uid = $request->input('uid');
      $type_id = $request->input('type_id');
      $limit = 10;
      $page = $request->input('page')?$request->input('page')-1:0;
      $pagex = $page*$limit;

      if($type_id == 1){
        // 按更新量->关注量->普通排序
        $teacherlist = DB::table('wj_tradition_teacher')->where('is_mingshi',1)->offset($pagex)->limit($limit)->get();
        // dd($teacherlist);
        $list = [];
        $list = getTeacher($teacherlist,$uid);
        for($i=0;$i<count($list);$i++){
          for($j=0;$j<count($list);$j++){
            if($list[$i]['is_attention'] > $list[$j]['is_attention']){
              $t = $list[$i];
              $list[$i] = $list[$j] ;
              $list[$j] = $t;
            }
          }
        } 
        for($i=0;$i<count($list);$i++){
          for($j=0;$j<count($list);$j++){
            if($list[$i]['is_update'] > $list[$j]['is_update']){
              $t = $list[$i];
              $list[$i] = $list[$j] ;
              $list[$j] = $t;
            }
          }
        } 
      }else if($type_id == 2){
        // 推荐名师
        $recolist1 = DB::table('wj_tradition_teacher')->where('is_tuijian',1)->offset($pagex)->limit($limit)->get();
        // 推荐视频
        $recolist2 = DB::table('wj_tradition_video')->where('is_tuijian',1)->where('is_close',0)->offset($pagex)->limit($limit)->get();
        $list1 = getTeacher($recolist1,$uid);
        $recolist = [];
        foreach($list1 as $key=>$value){
          $recolist[] = $value;
        }
        $list2 = getVideo($recolist2,$uid);
        foreach($list2 as $key=>$value){
          $recolist[] = $value;
        }
        for($i=0;$i<count($recolist);$i++){
          for($j=0;$j<count($recolist);$j++){
            if($recolist[$i]['plays'] > $recolist[$j]['plays']){
              $t = $recolist[$i];
              $recolist[$i] = $recolist[$j] ;
              $recolist[$j] = $t;
            }
          }
        } 
        $list= $recolist;      
      }else if($type_id >= 3){
        // $recolist1 = DB::table('wj_tradition_teacher')->where('type_id1',$type_id)->orWhere('type_id2',$type_id)->orWhere('type_id3',$type_id)->get();
        // 视频
        $recolist2 = DB::table('wj_tradition_video')->where('type_id',$type_id)->where('is_close',0)->orderBy('add_time','DESC')->offset($pagex)->limit($limit)->get();
        // $list1 = getTeacher($recolist1,$uid);
        $list = [];
        // foreach($list1 as $key=>$value){
        //   $recolist[] = $value;
        // }
        $list = getVideo($recolist2,$uid);
        // // 按播放量/关注量排序
        // for($i=0;$i<count($recolist);$i++){
        //   for($j=0;$j<count($recolist);$j++){
        //     if($recolist[$i]['add_time'] > $recolist[$j]['add_time']){
        //       $t = $recolist[$i];
        //       $recolist[$i] = $recolist[$j] ;
        //       $recolist[$j] = $t;
        //     }
        //   }
        // }  
        // $list = $recolist;     
      }
      if($list){
        $a['state']=1;
        $a['msg']="成功";
        $a['data']=$list;
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="失败";
        $a['data']=array();
        $a=json_encode($a);
        return $a;
      }
    }

    // 导航
    public function postNavigation(Request $request){
      $navilist = DB::table('wj_tradition_navigation')->get();
      if($navilist){
        $a['state']=1;
        $a['msg']="成功";
        $a['data']=$navilist;
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="失败";
        $a['data']=array();
        $a=json_encode($a);
        return $a;
      }
    }

    // 关注/取消关注/更新
    public function postAttente(Request $request){    
      $list['uid'] = $uid = $request->input('uid');
      $list['teacher_id'] = $teacher_id = $request->input('teacher_id');
      $is_attention = $request->input('is_attention');
      if($is_attention == 1){
        // 关注量+1
        $plays = DB::table('wj_tradition_teacher')->where('id',$teacher_id)->value('plays');
        $plays += 1;
        DB::table('wj_tradition_teacher')->where('id',$teacher_id)->update(array('plays'=>$plays));
        // 插入关注表
        $info = DB::table('wj_tradition_attention')->insert($list);        
      }else if($is_attention == 2){
        // 关注量-1
        $plays = DB::table('wj_tradition_teacher')->where('id',$teacher_id)->value('plays');
        $plays -= 1;
        DB::table('wj_tradition_teacher')->where('id',$teacher_id)->update(array('plays'=>$plays));
        // 删除关注
        $info = DB::table('wj_tradition_attention')->where('uid',$uid)->where('teacher_id',$teacher_id)->delete();        
      }else if($is_attention == 3){
        // 更新量+1
        $updates = DB::table('wj_tradition_teacher')->where('id',$teacher_id)->value('updates');
        $updates += 1;
        DB::table('wj_tradition_teacher')->where('id',$teacher_id)->update(array('updates'=>$updates));
        // is_upodate改为1
        $info = DB::table('wj_tradition_attention')->where('teacher_id',$teacher_id)->where('uid',$uid)->update(array('is_update'=>1));       
      }

      if($info){
        $a['state']=1;
        $a['msg']="成功";
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="失败";
        $a=json_encode($a);
        return $a;
      }
    }

    // 播放/点赞/取消点赞
    public function postOperate(Request $request){ 
      $list['uid'] = $uid = $request->input('uid'); 
      $list['video_id'] = $video_id = $request->input('video_id');   
      $operation = $request->input('operation');
      if($operation == 1){
        // 播放量+1
        $plays = DB::table('wj_tradition_video')->where('id',$video_id)->value('plays');
        $plays += 1;
        $info = DB::table('wj_tradition_video')->where('id',$video_id)->update(array('plays'=>$plays));       
      }else if($operation == 2){
        // 点赞量+1
        $likes = DB::table('wj_tradition_video')->where('id',$video_id)->value('likes');
        $likes += 1;
        DB::table('wj_tradition_video')->where('id',$video_id)->update(array('likes'=>$likes)); 
        $info = DB::table('wj_tradition_like')->insert($list);        
      }else if($operation == 3){
        // 点赞量-1
        $likes = DB::table('wj_tradition_video')->where('id',$video_id)->value('likes');
        $likes -= 1;
        $info = DB::table('wj_tradition_video')->where('id',$video_id)->update(array('likes'=>$likes)); 
        $info = DB::table('wj_tradition_like')->where('uid',$uid)->where('video_id',$video_id)->delete();      
      } 
      if($info){
        $a['state']=1;
        $a['msg']="成功";
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="失败";
        $a=json_encode($a);
        return $a;
      }
    }

    // 视频详情
    public function postVideodetail(Request $request){
      $video_id = $list['video_id'] = $request->input('video_id');
      $uid = $list['uid'] = $request->input('uid'); 
      $is_free = DB::table('wj_tradition_video')->where('id',$video_id)->value('classprice');
      $count =  DB::table('wj_tradition_play')->where('video_id',$video_id)->where('uid',$uid)->count();
      if($count < 1){
        if($is_free == 0){
          DB::table('wj_tradition_play')->insert($list);
        }else{
          $list['is_buy'] = 1;
          DB::table('wj_tradition_play')->insert($list);
        }        
      }else{
         $is_buy = DB::table('wj_tradition_play')->where('video_id',$video_id)->where('uid',$uid)->value('is_buy');
         if($is_buy == 2){

         }else if($is_free == 0){
          DB::table('wj_tradition_play')->where('video_id',$video_id)->where('uid',$uid)->update(array('is_buy'=>0));
        }else{
          DB::table('wj_tradition_play')->where('video_id',$video_id)->where('uid',$uid)->update(array('is_buy'=>1));
        }        
      }

      // 视频详情
      $detaillist = DB::table('wj_tradition_video')
                    ->join('wj_tradition_teacher','wj_tradition_video.teacher_id','=','wj_tradition_teacher.id')
                    ->where('wj_tradition_video.id',$video_id)
                    ->select('wj_tradition_video.*','wj_tradition_teacher.avartar','wj_tradition_teacher.name')
                    ->first();
      $list = [];
      $list = getDetail($detaillist,$uid);

      if($list){
        $a['state']=1;
        $a['msg']="成功";
        $a['data']=$list;
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="失败";
        $a['data']=array();
        $a=json_encode($a);
        return $a;
      }
    }

    // 视频问答（修改过）
    public function postVideoask(Request $request){
      $uid = $request->input('uid');
      $video_id = $request->input('video_id');
      $teacherlist = DB::table('wj_tradition_teacher')
                     ->join('wj_tradition_video','wj_tradition_teacher.id','=','wj_tradition_video.teacher_id')
                     ->where('wj_tradition_video.id',$video_id)
                     ->select('wj_tradition_teacher.uid','wj_tradition_teacher.avartar','wj_tradition_teacher.name')
                     ->first();
      // dd($teacherlist );
      $limit = 10;
      $page = $request->input('page')?$request->input('page')-1:0;
      $pagex = $page*$limit;
      $asklist = DB::table('wj_tradition_ask')
           ->join('wj_members_info','wj_tradition_ask.uid','=','wj_members_info.uid')
           ->join('wj_members','wj_tradition_ask.uid','=','wj_members.uid')
           ->where('wj_tradition_ask.video_id',$video_id)
           ->orderBy('is_static','DESC')
           ->orderBy('comment_time','DESC')
           ->offset($pagex)
           ->limit($limit)
           ->select('wj_tradition_ask.*','wj_members_info.head_portrait','wj_members.nick_name')
           ->get();
      $arrlist = [];
      foreach($asklist as $key=>$value){
        $arrlist[$key]['id'] = $value->id;
        $arrlist[$key]['nick_name'] = $value->nick_name;
        $arrlist[$key]['head_portrait'] = 'http://app.putitt.com'.$value->head_portrait;
        $arrlist[$key]['comment'] = $value->comment;
        $arrlist[$key]['comment_time'] = $value->comment_time;
        $arrlist[$key]['teacher_name'] = $teacherlist->name;
        $arrlist[$key]['teacher_avartar'] = $teacherlist->avartar;
        $arrlist[$key]['reply'] = $value->reply?$value->reply:'';
        $arrlist[$key]['reply_time'] = $value->reply_time?$value->reply_time:'';
        // $arrlist[$key]['reply_voice'] = 
        $arrlist[$key]['voice'][] = $value->reply_voice1?$value->reply_voice1:'';
        $arrlist[$key]['voice'][] = $value->reply_voice2?$value->reply_voice2:'';
        $arrlist[$key]['voice'][] = $value->reply_voice3?$value->reply_voice3:'';
        $arrlist[$key]['voice_long'][] = $value->voice1_long?$value->voice1_long:'';
        $arrlist[$key]['voice_long'][] = $value->voice2_long?$value->voice2_long:'';
        $arrlist[$key]['voice_long'][] = $value->voice3_long?$value->voice3_long:'';

        $i = 0;
        if(empty($value->reply_voice1) && empty($value->reply_voice2) && empty($value->reply_voice3)){
          $arrlist[$key]['reply_voice'] = [];
        }else{
          foreach($arrlist[$key]['voice'] as $k=>$val){
            if($val == ''){
     
            }else{
              $arrlist[$key]['reply_voice'][$i]['reply_voice'] = $val ;
              $arrlist[$key]['reply_voice'][$i]['voice_long'] = $arrlist[$key]['voice_long'][$k];
              $i++;
            }
          }          
        }

        if(empty($value->reply) && empty($value->reply_voice1) && empty($value->reply_voice2) && empty($value->reply_voice3) && $value->uid == $uid){
        // if($value->uid == $uid){
          $arrlist[$key]['is_delcomment'] = 1; // 用户是否有权限删除评论 0没权限 1有权限
        // }          
        }else{
          $arrlist[$key]['is_delcomment'] = 0;
        }
        if((!empty($value->reply) || !empty($value->reply_voice1) || !empty($value->reply_voice2) || !empty($value->reply_voice3)) && $teacherlist->uid == $uid){
        // if($value->uid == $uid){
          $arrlist[$key]['is_delreply'] = 1; // 用户是否有权限删除评论 0没权限 1有权限
        // }          
        }else{
          $arrlist[$key]['is_delreply'] = 0;
        }
        if(empty($value->reply) && (empty($value->reply_voice1) || empty($value->reply_voice2) || empty($value->reply_voice3)) && $uid == $teacherlist->uid){
          $arrlist[$key]['is_reply'] = 1; // 0不可回复 1可回复
        }else{
          $arrlist[$key]['is_reply'] = 0; 
        }
      }
      if($arrlist){
        $a['state']=1;
        $a['msg']="成功";
        $a['data']=$arrlist;
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="失败";
        $a['data']=array();
        $a=json_encode($a);
        return $a;
      }
    }

    // 相关视频
    public function postVideorelate(Request $request){
      $video_id = $request->input('video_id');
      $uid = $request->input('uid');
      $limit = 10;
      $page = $request->input('page')?$request->input('page')-1:0;
      $pagex = $page*$limit;
      $type_id = DB::table('wj_tradition_video')->where('id',$video_id)->value('type_id');
      $relatedlist = DB::table('wj_tradition_video')->where('type_id',$type_id)->where('id','!=',$video_id)->offset($pagex)->limit($limit)->get(); 
      $related = [];
      $related = getVideo($relatedlist,$uid);
      if($related){
        $a['state']=1;
        $a['msg']="成功";
        $a['data']=$related;
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="失败";
        $a['data']=array();
        $a=json_encode($a);
        return $a;
      }     
    }

    // 视频评论
    public function postComment(Request $request){
      $list['uid'] = $request->input('uid'); 
      $list['video_id'] = $request->input('video_id');
      $list['comment'] = $request->input('comment');
      $list['comment_time'] = time().'000';
      $info = DB::table('wj_tradition_ask')->insert($list);
      if($info){
        $a['state']=1;
        $a['msg']="成功";
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="失败";
        $a=json_encode($a);
        return $a;
      }
    }

    // 名师详情
    public function postTeacherdetail(Request $request){
      $uid = $request->input('uid');
      $teacher_id = $request->input('teacher_id');
      $detaillist = DB::table('wj_tradition_teacher')->where('id',$teacher_id)->first();
      $list['id'] = $detaillist->id;
      $list['name'] = $detaillist->name;
      $list['avartar'] = $detaillist->avartar?$detaillist->avartar:'';
      $list['imgpath'] = $detaillist->imgpath?ltrim($detaillist->imgpath,' '):'';
      $list['teacher_abstract'] = $detaillist->teacher_abstract;
      $list['updates'] = $detaillist->updates;
      $list['plays'] = $detaillist->plays;
      $list['keyword1'] = DB::table('wj_tradition_type')->where('id',$detaillist->type_id1)->value('name');
      $list['keyword2'] = DB::table('wj_tradition_type')->where('id',$detaillist->type_id2)->value('name');
      $list['keyword3'] = DB::table('wj_tradition_type')->where('id',$detaillist->type_id3)->value('name');
      $list['keyword2'] = $list['keyword2']?$list['keyword2']:'';
      $list['keyword3'] = $list['keyword3']?$list['keyword3']:'';
      $list['add_time'] = $detaillist->add_time;
      // 是否关注
      $list['is_attention'] = DB::table('wj_tradition_attention')->where('uid',$uid)->where('teacher_id',$detaillist->id)->count();
      $list['is_attention'] = $list['is_attention']?$list['is_attention']:0;
      // 是否更新
      $list['is_update'] = DB::table('wj_tradition_attention')->where('uid',$uid)->where('teacher_id',$detaillist->id)->value('is_update');
      $list['is_update'] = $list['is_update']?$list['is_update']:0;
      // 课时数
      $list['class'] = DB::table('wj_tradition_video')->where('teacher_id',$detaillist->id)->where('is_close',0)->count();
      $list['is_teacher'] = 1;
      // $videolist = DB::table('wj_tradition_video')->where('teacher_id',$teacher_id)->where('is_close',0)->get();
      // $list['videolist'] = getVideo($videolist,$uid);
      if($list){
        $a['state']=1;
        $a['msg']="成功";
        $a['data']=$list;
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="失败";
        $a['data']=array();
        $a=json_encode($a);
        return $a;
      }
    }

    // 全部视频
    public function postTeachervideo(Request $request){
      $uid = $request->input('uid');
      $teacher_id = $request->input('teacher_id');
      $limit = 10;
      $page = $request->input('page')?$request->input('page')-1:0;
      $pagex = $page*$limit;
      $videolist = DB::table('wj_tradition_video')->where('teacher_id',$teacher_id)->where('is_close',0)->offset($pagex)->limit($limit)->get();
      $list = [];
      $list = getVideo($videolist,$uid);
      if($list){
        $a['state']=1;
        $a['msg']="成功";
        $a['data']=$list;
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=1;
        $a['msg']="暂无数据";
        $a['data']=array();
        $a=json_encode($a);
        return $a;
      }
    }

    // 购买视频
    public function postPayvideo(Request $request){
      $amount = $request->input('amount');
      $video_id = $list['video_id'] = $request->input('video_id');
      $uid = $list['uid'] = $request->input('uid'); 
      $balance = DB::table('wj_members_account')->where('uid',$uid)->value('balance');
      $balance = $balance - $amount;
      $data['balance'] = $balance;
      // dd( $list);
      $info = DB::table('wj_tradition_play')->where('uid',$uid)->where('video_id',$video_id)->update(array('is_buy'=>2));
      if($balance >= 0){
        DB::table('wj_members_account')->where('uid',$uid)-> update($data);
        $a['state']=1;
        $a['msg']="成功";
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="余额不足";
        $a=json_encode($a);
        return $a;
      }
    }

    // 文字回复
    public function postReplytext(Request $request){
      $ask_id = $request->input('ask_id');
      $list['reply'] = $request->input('reply');
      $list['reply_time'] = time().'000';
      $info = DB::table('wj_tradition_ask')->where('id',$ask_id)->update($list);
      if($info){
        $a['state']=1;
        $a['msg']="成功";
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="失败";
        $a=json_encode($a);
        return $a;
      }
    }    
    // 语音回复
    public function postReplyvoice(Request $request){
      $ask_id = $request->input('ask_id');
      $reply_voice = $request->input('reply_voice');
      $voice_long = $request->input('voice_long');
      $reply_voice1 = DB::table('wj_tradition_ask')->where('id',$ask_id)->value('reply_voice1');
      $reply_voice2 = DB::table('wj_tradition_ask')->where('id',$ask_id)->value('reply_voice2');
      $reply_voice3 = DB::table('wj_tradition_ask')->where('id',$ask_id)->value('reply_voice3');
      if(empty($reply_voice1)){       
        $list['reply_voice1'] = $reply_voice;
        $list['voice1_long'] = $voice_long;
        $list['reply_time'] = time().'000'; 
        $info = DB::table('wj_tradition_ask')->where('id',$ask_id)->update($list);             
      }else if(empty($reply_voice2)){       
        $list['reply_voice2'] = $reply_voice;
        $list['voice2_long'] = $voice_long;
        $list['reply_time'] = time().'000'; 
        $info = DB::table('wj_tradition_ask')->where('id',$ask_id)->update($list); 
      }else{        
        $list['reply_voice3'] = $reply_voice;
        $list['voice3_long'] = $voice_long;
        $list['reply_time'] = time().'000'; 
        $info = DB::table('wj_tradition_ask')->where('id',$ask_id)->update($list);        
      }
      if(empty($reply_voice1) && empty($reply_voice2) && empty($reply_voice3)){
        $reply_count = 1;
      }else if(empty($reply_voice1) && empty($reply_voice2)){
        $reply_count = 2;
      }else if(empty($reply_voice1) && empty($reply_voice3)){
        $reply_count = 2;
      }else if(empty($reply_voice2) && empty($reply_voice3)){
        $reply_count = 2;
      }else{
        $reply_count = 3;        
      }


      if($info){
        $a['state']=1;
        $a['msg']="成功";
        $a['reply_count']=$reply_count;
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="失败";
        $a=json_encode($a);
        return $a;
      }
    } 
    // 删除评论
    public function postDeletecomment(Request $request){
      $id = $request->input('ask_id');
      $info = DB::table('wj_tradition_ask')->where('id',$id)->delete();
      if($info){
        $a['state']=1;
        $a['msg']="成功";
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="失败";
        $a=json_encode($a);
        return $a;
      }
    }
    // 删除回复
    public function postDeletetext(Request $request){
      $id = $request->input('ask_id');
      $list['reply'] = '';
      $info = DB::table('wj_tradition_ask')->where('id',$id)->update($list);
      if($info){
        $a['state']=1;
        $a['msg']="成功";
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="失败";
        $a=json_encode($a);
        return $a;
      }
    }
    // 删除回复
    public function postDeletevoice(Request $request){
      $id = $request->input('ask_id');
      $reply_voice = $request->input('reply_voice');
      $replylist = DB::table('wj_tradition_ask')->where('id',$id)->first();
      if($reply_voice == 1){
        if(!empty($replylist->reply_voice1)){
          $list['reply_voice1'] = '';
          $list['voice1_long'] = '';
        }else if(!empty($replylist->reply_voice2)){
          $list['reply_voice2'] = '';
          $list['voice2_long'] = '';
        }else{
          $list['reply_voice3'] = '';
          $list['voice3_long'] = '';
        }        
      }else if($reply_voice == 1){
        if(!empty($replylist->reply_voice1) && !empty($replylist->reply_voice2)){
          $list['reply_voice2'] = '';
          $list['voice2_long'] = '';
        }else if(!empty($replylist->reply_voice1) && !empty($replylist->reply_voice3)){
          $list['reply_voice3'] = '';
          $list['voice3_long'] = '';
        }else if(!empty($replylist->reply_voice2) && !empty($replylist->reply_voice3)){
          $list['reply_voice3'] = '';
          $list['voice3_long'] = '';
        }        
      }else{
        $list['reply_voice3'] = '';
        $list['voice3_long'] = '';
      }

      
      $info = DB::table('wj_tradition_ask')->where('id',$id)->update($list);
      if($info){
        $a['state']=1;
        $a['msg']="成功";
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="失败";
        $a=json_encode($a);
        return $a;
      }
    }
    public function postVoice(Request $request)
    {
      if($request->hasFile('img')){
        // $a['size']=$_FILES['img']['size'];
        $path='./uploads/'.date('Ymd').'/';
        //echo str_random(50);
        //或得文件的扩展名
        $e=$request->file('img')->getClientOriginalExtension();
        //获得随机数的文件名
        //echo $e;
        $rand=time().str_random(20);
        if(empty($e)){
            $e="mp3";
        }
        //拼装好以后的真正的文件名
        $fileName=$rand.".".$e;
        //拼接一个图片存入数据库的路径 拼接一个绝对路径
        $dir=trim($path.$fileName,'.');
        $request->file('img')->move($path,$fileName);
        $b['lujing']=$dir;
          $a['e']=$e;
          $a['state']="1";
          $a['msg']="上传成功";
          $a['data']=$b;
      }else{
        $a['state']="0";
        $a['msg']="上传失败";
        $a['data']=array();
      }
      return json_encode($a);
    }
    public function postVoice1(Request $request)
    {
      if($request->hasFile('img')){
        // $a['size']=$_FILES['img']['size'];
        $path='./uploads/'.date('Ymd').'/';
        //echo str_random(50);
        //或得文件的扩展名
        $e=$request->file('img')->getClientOriginalExtension();
        //获得随机数的文件名
        //echo $e;
        $rand=time().str_random(20);
        if(empty($e)){
            $e="MP3";
        }
        //拼装好以后的真正的文件名
        $fileName=$rand.".".$e;
        //拼接一个图片存入数据库的路径 拼接一个绝对路径
        $dir=trim($path.$fileName,'.');
        $request->file('img')->move($path,$fileName);
        $b['lujing']=$dir;
          $a['e']=$e;
          $a['state']="1";
          $a['msg']="上传成功";
          $a['data']=$b;
      }else{
        $a['state']="0";
        $a['msg']="上传失败";
        $a['data']=array();
      }
      return json_encode($a);
    }
    public function postIndex(Request $request){
      $list = [];
      // $res1 = [];
      $res = DB::table('wj_tradition_teacher')->orderBy('plays','DESC')->select('id','imgpath')->first();
      // $res1['teacher_id'] = $res->
      $list['teacher'] = ['teacher_id'=>$res->id?$res->id:'','imgpath'=>$res->imgpath?$res->imgpath:''];
      $data1 = [];
      $data = DB::table('wj_tradition_video')->orderBy('plays','DESC')->limit(3)->select('id','videoimg')->get();
      foreach ($data as $key => $value) {
          $data1['video_id'] = $value->id?$value->id:'';
          $data1['videoimg'] = $value->videoimg?$value->videoimg:'';
          $list['video'][] = $data1;
      }
      if($list){
        $a['state']=1;
        $a['msg']="成功";
        $a['data']=$list;
        $a=json_encode($a);
        return $a;
      }else{
        $a['state']=0;
        $a['msg']="失败";
        $a=json_encode($a);
        return $a;
      }
    }
}
