<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use App\Models\User;
use Auth;


class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth',[
            'except' => ['show','create','store','index']
            //通过 except 方法来设定 指定动作 不使用 Auth 中间件进行过滤，
            //意为 —— 除了此处指定的动作以外，所有其他动作都必须登录用户才能访问，
            //类似于黑名单的过滤机制。相反的还有 only 白名单方法，将只过滤指定动作。
            //我们提倡在控制器 Auth 中间件使用中，首选 except 方法，
            //这样的话，当你新增一个控制器方法时，默认是安全的，此为最佳实践。
        ]);
        //在 __construct 方法中调用了 middleware 方法，
        //该方法接收两个参数，第一个为中间件的名称，第二个为要进行过滤的动作

        $this->middleware('guest',[
            'only' => ['create']
        ]);
    }

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

        Auth::login($user);

        session()->flash('success','欢迎老弟，你将在这里开启一段新的旅程~');
        return redirect()->route('users.show',[$user]);
        //validate()接收两个参数
        //参数一是用户的输入数据
        //参数二是输入数据的验证规则
    }

    public function edit(User $user)
    {
        $this->authorize('update',$user);
        return view('users.edit',compact('user'));
    }

    public function update(User $user,Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:50',
            'password' => 'required|confirmed|min:6'
        ]);

        $this->authorize('update',$user);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password){
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success','个人资料更新成功！');

        return redirect()->route('users.show',$user->id);
    }

    public function index()
    {
        $users = User::paginate(10);
        //paginate()方法用来指定每页生成的数据数量为10条
        return view('users.index',compact('users'));
    }

    public function destory(User $user)
    {
        $this->authorize('destroy',$user);//
        $user->delete();
        session()->flash('success','成功删除用户！');
        return back();
    }
}
