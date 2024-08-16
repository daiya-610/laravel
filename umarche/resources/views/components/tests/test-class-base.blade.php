<div>
    クラスベースのコンポーネントです。
    使用する場合は
    App/View/Components内のクラスを指定する。
    クラス名・・・TestClassBase(パスカルケース)
    Blade内・・・x-test-class-base(ケバブケース)

    コンポーネントクラス内で
    public funtion render(){
        return view('bladeコンポーネント名')
    }
    <div>{{ $classBaseMessage }}</div>
    <div>{{ $defaultMessage }}</div>
</div>