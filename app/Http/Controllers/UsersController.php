<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;


class UsersController extends Controller
{
    public function create()
    {
    	return view('users.create');
    }

    public function show(User $user)
    {
        return view('users.show',compact('user'));
    }
    //Laravel 会自动解析定义在控制器方法（变量名匹配路由片段）中的 Eloquent 模型类型声明。在上面代码中，由于 show() 方法传参时声明了类型 —— Eloquent 模型 User，对应的变量名 $user 会匹配路由片段中的 {user}，这样，Laravel 会自动注入与请求 URI 中传入的 ID 对应的用户模型实例。
    //
    //此功能称为 『隐性路由模型绑定』，是『约定优于配置』设计范式的体现，同时满足以下两种情况，此功能即会自动启用：
    //
    //路由声明时必须使用 Eloquent 模型的单数小写格式来作为路由片段参数，User 对应 {user}：

    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);
        //required用来验证是否为空

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        session()->flash('success','欢迎老弟，你将在这里开启一段新的旅程~');
        return redirect()->route('users.show',[$user]);
        //validate()接收两个参数
        //参数一是用户的输入数据
        //参数二是输入数据的验证规则
    }
}
