<?php
// // +----------------------------------------------------------------------
// // | OneThink [ WE CAN DO IT JUST THINK IT ]
// // +----------------------------------------------------------------------
// // | Copyright (c) 2013 http://www.onethink.cn All rights reserved.
// // +----------------------------------------------------------------------
// // | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// // +----------------------------------------------------------------------

namespace Home\Controller;

// /**
//  * 文档模型控制器
//  * 文档模型列表和详情
//  */
class ArticleController extends HomeController {
	
	// public function newslist(){
 //        $channel_id = I('hid');
 //        $category_id = I('cid');
 //        $channelname = M('Channel')->where(array('id'=>$channel_id))->getField('title');
 //        $pcategory_id = M('Category')->where(array('name'=>$channelname))->getField('id');
 //        $category_name = M('Category')->where(array('id'=>$category_id))->getField('name');
 //        $channel_content = M('Channel')->where(array('id'=>$channel_id))->find();
 //        $category_list = M('Category')->where(array('pid'=>$pcategory_id))->select();
 //        $documents = M('document')->where(array('category_id'=>$category_id))->select();
 //        $this->assign('channelname',$channelname);
 //        $this->assign('channel_content',$channel_content);
 //        $this->assign('category_list',$category_list);
 //        $this->assign('document',$document);
 //        $this->assign('category_name',$category_name);
 //        $this->assign('documents',$documents);
 //        $this->assign('channel_id',$channel_id);
 //        $this->getCategories();
 //        $this->display();
    
	// }


    //有列表页的newsdetail
    // public function newsdetail(){
    //     $document_id = I('did');
    //     $document = M('Document')->where(array('id'=>$document_id))->find();
    //     $article_title = $document['title'];
    //     $document_article = M('Document_article')->where(array('id'=>$document_id))->find();
    //     $category_id = $document['category_id'];
    //     $category_name = M('Category')->where(array('id'=>$category_id))->getField('name');
    //     $pcategory = M('Category')->where(array('id'=>$category_id))->getField('pid');
    //     $channelname=M('Category')->where(array('id'=>$pcategory))->getField('title');
    //     $hid = M('Channel')->where(array('title'=>$channelname))->getField('id');
    //     $category_list = M('Category')->where(array('pid'=>$pcategory))->select();
    //     $this->assign('document',$document); 
    //     $this->assign('document_article',$document_article);
    //     $this->assign('channelname',$channelname);
    //     $this->assign('category_list',$category_list);
    //     $this->assign('hid',$hid);
    //     $this->assign('category_name',$category_name);
    //     $this->assign('article_title',$article_title);
    //     $this->getCategories();
    //     $this->display();
    // }

    //无列表页的newsdetail
    public function newsdetail(){
        $category_id = I('cid');
        $channel_id = I('hid');
        $document = M('Document')->where(array('category_id'=>$category_id))->find();
        $article_title = $document['title'];
        $document_article = M('Document_article')->where(array('id'=>$document['id']))->find();
        $category_name = M('Category')->where(array('id'=>$category_id))->getField('name');
        $pcategory = M('Category')->where(array('id'=>$category_id))->getField('pid');
        $channelname=M('Category')->where(array('id'=>$pcategory))->getField('title');
        $hid = M('Channel')->where(array('title'=>$channelname))->getField('id');
        $category_list = M('Category')->where(array('pid'=>$pcategory))->select();
        
        $next = M('Document')->where(array('id'=>($document['id']+1)))->find();
        if(!empty($next))
        {
            $nextLink = U('Home/Article/newsdetail',array('cid'=>$next['category_id']));
            $nextTitle = $next['title'];
        }
        else
        {
            $nextLink = '#';
            $nextTitle = "Nothing behind";
        }

        $prev = M('Document')->where(array('id'=>($document['id']-1)))->find();
        if(!empty($prev))
        {
            $prevTitle = $prev['title'];
            $prevLink = U('Home/Article/newsdetail',array('cid'=>$prev['category_id']));
        }
        else
        {
            $prevTitle = 'Nothing before';
            $prevLink = '#';
        }
        
        $this->assign('_prev_title',$prevTitle);
        $this->assign('_next_title',$nextTitle);
        $this->assign('_prev_link',$prevLink);
        $this->assign('_next_link',$nextLink);
        $this->assign('document',$document); 
        $this->assign('document_article',$document_article);
        $this->assign('channelname',$channelname);
        $this->assign('category_list',$category_list);
        $this->assign('hid',$hid);
        $this->assign('category_name',$category_name);
        $this->assign('article_title',$article_title);
        $this->getCategories();
        $this->display();
    } 


    public function keywords_searchlist(){
        $keywords = I('post.keywords');
        $lists = array();
        $keywordslist = array();
        $Document = M('Document');
        $keywordsid = $Document->join('gperf_document_article ON gperf_document.id = gperf_document_article.id')->where("gperf_document_article.content like '%$keywords%' or gperf_document_article.content like '%$keywords%'")->field('gperf_document.category_id')->select();
        foreach ($keywordsid as $value) {
            # code...
        $list = M('Category')->where(array('id'=>$value['category_id']))->select();
        $lists = array_merge($list,$lists);
    }
         
        $lists = array_map(function($list){ 
            $list['cid'] = $list['id'];
            $pcategory_id = M('Category')->where(array('id'=>$list['id']))->getField('pid');
            $channelname = M('Category')->where(array('id'=>$pcategory_id))->getField('title');
            $list['hid'] = M('Channel')->where(array('title'=>$channelname))->getField('id');
            return $list;
        },$lists);
    $this->assign('lists',$lists);
    $this->getCategories();
    $this->display();
     
    }
}
    // public function index(){
    // 	$channel = M('channel');
    // 	$category = M('category');
    // 	this->assign('channel',$channel);
    // 	this->display();
    // 	this->assign('category',$category);
    // 	this->display();
    
//     /* 文档模型频道页 */
// 	public function index(){
// 		/* 分类信息 */
// 		$category = $this->category();

// 		//频道页只显示模板，默认不读取任何内容
// 		//内容可以通过模板标签自行定制

// 		/* 模板赋值并渲染模板 */
// 		$this->assign('category', $category);
// 		$this->display($category['template_index']);
// 	}

// 	/* 文档模型列表页 */
// 	public function lists($p = 1){
// 		/* 分类信息 */
// 		$category = $this->category();

// 		/* 获取当前分类列表 */
// 		$Document = D('Document');
// 		$list = $Document->page($p, $category['list_row'])->lists($category['id']);
// 		if(false === $list){
// 			$this->error('获取列表数据失败！');
// 		}

// 		/* 模板赋值并渲染模板 */
// 		$this->assign('category', $category);
// 		$this->assign('list', $list);
// 		$this->display($category['template_lists']);
// 	}

// 	/* 文档模型详情页 */
// 	public function detail($id = 0, $p = 1){
// 		/* 标识正确性检测 */
// 		if(!($id && is_numeric($id))){
// 			$this->error('文档ID错误！');
// 		}

// 		/* 页码检测 */
// 		$p = intval($p);
// 		$p = empty($p) ? 1 : $p;

// 		/* 获取详细信息 */
// 		$Document = D('Document');
// 		$info = $Document->detail($id);
// 		if(!$info){
// 			$this->error($Document->getError());
// 		}

// 		/* 分类信息 */
// 		$category = $this->category($info['category_id']);

// 		/* 获取模板 */
// 		if(!empty($info['template'])){//已定制模板
// 			$tmpl = $info['template'];
// 		} elseif (!empty($category['template_detail'])){ //分类已定制模板
// 			$tmpl = $category['template_detail'];
// 		} else { //使用默认模板
// 			$tmpl = 'Article/'. get_document_model($info['model_id'],'name') .'/detail';
// 		}

// 		/* 更新浏览数 */
// 		$map = array('id' => $id);
// 		$Document->where($map)->setInc('view');

// 		/* 模板赋值并渲染模板 */
// 		$this->assign('category', $category);
// 		$this->assign('info', $info);
// 		$this->assign('page', $p); //页码
// 		$this->display($tmpl);
// 	}

// 	/* 文档分类检测 */
// 	private function category($id = 0){
// 		/* 标识正确性检测 */
// 		$id = $id ? $id : I('get.category', 0);
// 		if(empty($id)){
// 			$this->error('没有指定文档分类！');
// 		}

// 		/* 获取分类信息 */
// 		$category = D('Category')->info($id);
// 		if($category && 1 == $category['status']){
// 			switch ($category['display']) {
// 				case 0:
// 					$this->error('该分类禁止显示！');
// 					break;
// 				//TODO: 更多分类显示状态判断
// 				default:
// 					return $category;
// 			}
// 		} else {
// 			$this->error('分类不存在或被禁用！');
// 		}
// 	}

// }

