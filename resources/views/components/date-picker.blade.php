@props(['placeholder' => 'カレンダーから選択'])

<div
    {{-- Alpine.jsのデータ定義エリア --}}
    x-data="{
        {{-- @entangle: Livewireのプロパティと、このAlpineの 'value' を双方向同期させる --}}
        value: @entangle($attributes->wire('model')),
        instance: null, // Flatpickrの本体（インスタンス）を保存しておく変数

        init() {
            // Flatpickrの初期化（$refs.input は下の <input> タグを指す）
            this.instance = flatpickr($refs.input, {
                locale: 'ja',         // 日本語化
                dateFormat: 'Y-m-d',  // データベース保存用フォーマット（yyyy-mm-dd）

                {{-- 表示用の工夫 --}}
                altInput: true,       // 表示用入力欄を別途作るモード（これにすると見た目を変えられる）
                altFormat: 'Y-m-d',   // ユーザーに見せるフォーマット（必要に応じて 'Y年m月d日' などに変更可能）
                allowInput: false,    // 手入力を禁止（カレンダー選択のみに強制）

                defaultDate: this.value, // 画面を開いた時の初期値をセット

                {{-- カレンダーで日付を選んだ時の動作 --}}
                onChange: (selectedDates, dateStr) => {
                    // 選ばれた日付文字列を Alpineの変数に入れる
                    // → @ entangle しているので、自動的にLivewire側にも伝わる
                    this.value = dateStr;
                },

                {{-- カレンダーの描画準備が完了した時の動作（カスタムボタンの注入） --}}
                onReady: (selectedDates, dateStr, instance) => {
                    // ボタンを入れる「箱」を作る（Flexboxで左右配置）
                    const container = document.createElement('div');
                    container.style.cssText = 'display: flex; justify-content: space-between; padding: 10px; border: 1px solid #dcdcdc; border-top: none; border-radius: 0 0 5px 5px; background-color: #ffffff;';

                    // ------------------------------------------------
                    //  「クリア」ボタン作成（左側）
                    // ------------------------------------------------
                    const clearBtn = document.createElement('div');
                    clearBtn.innerHTML = 'クリア';
                    clearBtn.style.cssText = 'cursor: pointer; color: #ff5555; font-weight: bold; font-size: 14px;';

                    clearBtn.addEventListener('click', () => {
                        instance.clear(); // Flatpickrの機能で日付をクリア
                        instance.close(); // カレンダーを閉じる
                    });

                    // ------------------------------------------------
                    //  「今日」ボタン作成（右側）
                    // ------------------------------------------------
                    const todayBtn = document.createElement('div');
                    todayBtn.innerHTML = '今日';
                    todayBtn.style.cssText = 'cursor: pointer; color: #2563eb; font-weight: bold; font-size: 14px;';

                    todayBtn.addEventListener('click', () => {
                        // ★重要: setDateの第2引数 'true'
                        // 第2引数を true にすると 'onChange' イベントが発火し、Livewireへのデータ同期が行われる（falseだと見た目だけ変わってデータ保存されない）
                        instance.setDate(new Date(), true);
                        instance.close();
                    });

                    // ------------------------------------------------
                    // 箱に入れて、ボタン2つをカレンダーの下部に追加
                    // ------------------------------------------------
                    container.appendChild(clearBtn);
                    container.appendChild(todayBtn);

                    // Flatpickrが生成したカレンダーコンテナの中に、自作ボタンを追加
                    instance.calendarContainer.appendChild(container);
                }
            });

            // Livewireからの変更を監視する（外部からの変更対応）
            // 保存処理後のリセットなど、PHP側で値が変わった時にFlatpickrへ反映させる処理
            this.$watch('value', newValue => {
                if (!newValue) {
                    // 値が空になったら（クリアされたら）、カレンダー表示もクリア
                    this.instance.clear();
                } else {
                    // 値が入ったらカレンダーにセット
                    // ★重要: 第2引数 'false'
                    // ここは「Livewire→JS」の流れなので、onChange（JS→Livewire）を発火させる必要がない。
                    // もし true にすると「変更通知の無限ループ」になる恐れがあるため false にする。
                    this.instance.setDate(newValue, false);
                }
            });
        }
    }"
    x-init="init"
    wire:ignore {{-- LivewireのDOM更新対象から除外（Flatpickrが勝手に作った要素が消されないように守る） --}}
    @blur-picker.window="
        if (instance && instance.altInput) {
            instance.altInput.blur(); // 入力欄からフォーカスを外す（スマホキーボード対策など）
        }
    "
    class="w-full"
>
    <input
        x-ref="input" {{-- Flatpickrがターゲットにするための目印 --}}
        type="text"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'input input-bordered w-full bg-white']) }}
    />
</div>