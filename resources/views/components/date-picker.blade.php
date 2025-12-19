@props(['placeholder' => 'カレンダーから選択'])

<div
    x-data="{
        value: @entangle($attributes->wire('model')), // Livewireと同期
        instance: null,
        init() {
            // 1. Flatpickrの初期化
            this.instance = flatpickr($refs.input, {
                locale: 'ja',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'Y-m-d',
                allowInput: false,
                defaultDate: this.value, // 初期値をセット
                onChange: (selectedDates, dateStr) => {
                    this.value = dateStr; // カレンダー操作 → Livewireへ通知
                },
                onReady: (selectedDates, dateStr, instance) => {
                    // 1. ボタンを入れる「箱」を作る（左右に並べるため）
                    const container = document.createElement('div');
                    container.style.cssText = 'display: flex; justify-content: space-between; padding: 10px; border: 1px solid #dcdcdc; border-top: none; border-radius: 0 0 5px 5px; background-color: #ffffff;';

                    // ------------------------------------------------
                    // 2. 「クリア」ボタン作成（左側）
                    // ------------------------------------------------
                    const clearBtn = document.createElement('div');
                    clearBtn.innerHTML = 'クリア';
                    // 赤色で警告色っぽく
                    clearBtn.style.cssText = 'cursor: pointer; color: #ff5555; font-weight: bold; font-size: 14px;';

                    clearBtn.addEventListener('click', () => {
                        instance.clear(); // 日付を消す
                        instance.close(); // 閉じる
                    });

                    // ------------------------------------------------
                    // 3. 「今日」ボタン作成（右側）
                    // ------------------------------------------------
                    const todayBtn = document.createElement('div');
                    todayBtn.innerHTML = '今日';
                    // 青色（テーマカラーに合わせる）
                    todayBtn.style.cssText = 'cursor: pointer; color: #2563eb; font-weight: bold; font-size: 14px;';

                    todayBtn.addEventListener('click', () => {
                        // 本日の日付をセット
                        // 第2引数の true は「onChangeイベントを発火させる」という意味
                        // これがないとLivewire側に変更が伝わらないので重要！
                        instance.setDate(new Date(), true);
                        instance.close(); // 閉じる
                    });

                    // ------------------------------------------------
                    // 4. 箱に入れて、カレンダーに追加
                    // ------------------------------------------------
                    container.appendChild(clearBtn); // 左にクリア
                    container.appendChild(todayBtn); // 右に今日

                    instance.calendarContainer.appendChild(container);
                }

            });

            // ▼▼▼ 2. 監視機能の追加（ここが修正ポイント！）▼▼▼
            // Livewire側で変数がリセットされたら、ここが動きます
            this.$watch('value', newValue => {
                if (!newValue) {
                    // 値が空になったら、カレンダーもクリアする
                    this.instance.clear();
                } else {
                    // 値が入ったら（編集時など）、カレンダーにセットする
                    // 第2引数の false は「onChangeイベントを発火させない」という意味（無限ループ防止）
                    this.instance.setDate(newValue, false);
                }
            });
        }
    }"
    x-init="init"
    wire:ignore
    @blur-picker.window="
        if (instance && instance.altInput) {
            instance.altInput.blur(); // 表示用の入力欄からフォーカスを外す
        } else if (instance) {
             // altInputを使っていない場合のフォールバック
             // instance.input.blur();
        }
    "
    class="w-full"
>
<input
        x-ref="input"
        type="text"
        placeholder="{{ $placeholder }}"
        {{ $attributes->merge(['class' => 'input input-bordered w-full bg-white']) }}
    />
</div>