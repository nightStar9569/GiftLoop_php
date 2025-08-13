<?php $pageTitle = '管理者ダッシュボード - ギフトループ';
$pageDescription = 'ギフト投稿管理、会社ギフト管理、ユーザー監視を行う管理者ページ。';
include __DIR__ . '/includes/header.php'; ?>

<section class="admin-header" style="margin-top: 4.5rem;">
  <div class="container">
    <div class="admin-hero" style="text-align: center;">
      <h1><i class="fas fa-shield-alt"></i> 管理者ダッシュボード</h1>
      <p>ギフト投稿管理・会社ギフト管理・ユーザー監視</p>
    </div>
  </div>
</section>

<section class="admin-content">
  <div class="container">
    <div class="admin-tabs"><button class="admin-tab active" data-tab="gifts"><i class="fas fa-boxes"></i>
        ギフト管理</button><button class="admin-tab" data-tab="corporate"><i class="fas fa-building"></i>
        会社ギフト</button><button class="admin-tab" data-tab="users"><i class="fas fa-users"></i> ユーザー管理</button></div>

    <div id="tab-gifts" class="admin-panel active">
      <div class="form-card" style="margin-top: 2rem;">
        <div class="form-header">
          <h2><i class="fas fa-plus-circle"></i> ギフトを作成 / 編集</h2>
          <p>一般ギフトの商品情報を登録・更新します</p>
        </div>
        <form id="giftForm">
          <input type="hidden" id="giftFormId" />
          <div class="form-row">
            <div class="form-group"><label for="giftFormName">名前</label><input type="text" id="giftFormName" required
                placeholder="例: プレミアムチョコレート" /></div>
            <div class="form-group"><label for="giftFormPrice">価格 (¥)</label><input type="number" id="giftFormPrice"
                min="0" step="1" required placeholder="3000" /></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label for="giftFormCategory">カテゴリー</label><select id="giftFormCategory">
                <option value="food">食品・スイーツ</option>
                <option value="fashion">ファッション</option>
                <option value="beauty">美容・コスメ</option>
                <option value="electronics">家電・電子機器</option>
                <option value="books">書籍・文具</option>
                <option value="hobby">趣味・スポーツ</option>
                <option value="home">生活用品</option>
                <option value="other">その他</option>
              </select></div>
            <div class="form-group"><label for="giftFormStatus">状態</label><select id="giftFormStatus">
                <option value="created">作成済</option>
                <option value="sent">送信済</option>
                <option value="pending">保留中</option>
                <option value="accepted">受取済</option>
                <option value="rejected">拒否済</option>
                <option value="owned">所有済</option>
              </select></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label for="giftFormDesc">説明</label><input type="text" id="giftFormDesc"
                placeholder="任意: 詳細説明" /></div>
            <div class="form-group"><label for="giftFormImage">画像URL</label>
              <div class="input-wrapper"><input type="url" id="giftFormImage" style="display: none;" /><input
                  type="file" id="giftFormImageFile" accept="image/*" style="margin-left: .5rem;"></div>
              <small>ファイル選択でアップロードし、自動入力されます</small>
            </div>
            <div class="form-group"><label class="checkbox-label"><input type="checkbox" id="giftFormSuper" /><span
                  class="checkmark"></span>スーパープレゼント</label></div>
          </div>
          <div class="form-actions" style="justify-content: flex-end;"><button type="submit" class="btn-primary"
              id="giftFormSubmit"><i class="fas fa-save"></i> 保存</button><button type="button" class="btn-primary"
              id="giftFormReset" style="margin-left: .75rem;"><i class="fas fa-rotate"></i> リセット</button></div>
        </form>
      </div>

      <div class="admin-toolbar" style="margin-block: 2.5rem;">
        <div class="input-wrapper"><input type="text" id="giftSearch" placeholder="名前/カテゴリー"></div>
        <div class="chip-group" id="giftStatusFilters" aria-label="ステータスで絞り込む"></div>
        <div class="chip-group" id="giftCategoryFilters" aria-label="カテゴリーで絞り込む" style="margin-block: 1.5rem;"></div>
        <div class="sort-control"><label for="giftSort">並び替え</label><select id="giftSort">
            <option value="recent">新着</option>
            <option value="price_desc">価格（高い順）</option>
            <option value="price_asc">価格（安い順）</option>
            <option value="name_asc">名前（A→Z）</option>
          </select></div>
      </div>
      <div id="giftsList" class="admin-list" style="margin-block: 2.5rem;"></div>

      <!-- Edit Gift Modal -->
      <div id="giftEditModal"
        style="position: fixed; inset: 0; background: rgba(0,0,0,.5); display: none; align-items: center; justify-content: center; z-index: 2000; padding: 1rem;">
        <div class="form-card" style="width: 100%; max-width: 640px;">
          <div class="form-header">
            <h2><i class="fas fa-pen-to-square"></i> ギフトを編集</h2>
            <p>選択したギフトの情報を更新します</p>
          </div>
          <form id="giftEditForm">
            <input type="hidden" id="giftEditId" />
            <div class="form-row">
              <div class="form-group"><label for="giftEditName">名前</label><input type="text" id="giftEditName"
                  required /></div>
              <div class="form-group"><label for="giftEditPrice">価格 (¥)</label><input type="number" id="giftEditPrice"
                  min="0" step="1" required /></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label for="giftEditCategory">カテゴリー</label><select id="giftEditCategory">
                  <option value="food">食品・スイーツ</option>
                  <option value="fashion">ファッション</option>
                  <option value="beauty">美容・コスメ</option>
                  <option value="electronics">家電・電子機器</option>
                  <option value="books">書籍・文具</option>
                  <option value="hobby">趣味・スポーツ</option>
                  <option value="home">生活用品</option>
                  <option value="other">その他</option>
                </select></div>
              <div class="form-group"><label for="giftEditStatus">状態</label><select id="giftEditStatus">
                  <option value="created">作成済</option>
                  <option value="sent">送信済</option>
                  <option value="pending">保留中</option>
                  <option value="accepted">受取済</option>
                  <option value="rejected">拒否済</option>
                  <option value="owned">所有済</option>
                </select></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label for="giftEditDesc">説明</label><input type="text" id="giftEditDesc"
                  placeholder="任意: 詳細説明" /></div>
              <div class="form-group"><label for="giftEditImage">画像URL</label>
                <div class="input-wrapper"><input type="url" id="giftEditImage" style="display:none" /><input
                    type="file" id="giftEditImageFile" accept="image/*" style="margin-left: .5rem;"></div>
              </div>
              <div class="form-group"><label class="checkbox-label"><input type="checkbox" id="giftEditSuper" /><span
                    class="checkmark"></span>スーパープレゼント</label></div>
            </div>
            <div class="form-actions" style="justify-content: flex-end;"><button type="button" class="btn-primary"
                id="giftEditCancel" style="background: #999; border: none;"><i class="fas fa-times"></i>
                キャンセル</button><button type="submit" class="btn-primary" id="giftEditSave"
                style="margin-left: .75rem;"><i class="fas fa-save"></i> 更新</button></div>
          </form>
        </div>
      </div>
    </div>

    <div id="tab-corporate" class="admin-panel">
      <div class="form-card">
        <div class="form-header">
          <h2><i class="fas fa-plus-circle"></i> 会社ギフトを作成 / 編集</h2>
          <p>企業向けギフトテンプレートを登録・更新します</p>
        </div>
        <form id="corpForm">
          <input type="hidden" id="corpFormId" />
          <div class="form-row">
            <div class="form-group"><label for="corpTitle">タイトル</label><input type="text" id="corpTitle" required
                placeholder="例: 御中元ギフトセット" /></div>
            <div class="form-group"><label for="corpCategory">カテゴリー</label><select id="corpCategory">
                <option value="food">食品・スイーツ</option>
                <option value="fashion">ファッション</option>
                <option value="beauty">美容・コスメ</option>
                <option value="electronics">家電・電子機器</option>
                <option value="books">書籍・文具</option>
                <option value="hobby">趣味・スポーツ</option>
                <option value="home">生活用品</option>
                <option value="other">その他</option>
              </select></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label for="corpSupplyPerDay">日次供給数</label><input type="number"
                id="corpSupplyPerDay" min="0" step="1" placeholder="50" /></div>
            <div class="form-group"><label for="corpTotalSupply">総供給数</label><input type="number" id="corpTotalSupply"
                min="0" step="1" placeholder="1000" /></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label for="corpStartAt">開始日時</label><input type="datetime-local" id="corpStartAt"
                required /></div>
            <div class="form-group"><label for="corpEndAt">終了日時</label><input type="datetime-local" id="corpEndAt"
                required /></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label for="corpDescription">説明</label><input type="text" id="corpDescription"
                placeholder="任意: 詳細説明" /></div>
            <div class="form-group"><label for="corpImageUrl">画像URL</label>
              <div class="input-wrapper"><input type="url" id="corpImageUrl" style="display:none" /><input type="file"
                  id="corpImageFile" accept="image/*" style="margin-left: .5rem;"></div>
              <small>ファイル選択でアップロードし、自動入力されます</small>
            </div>
            <div class="form-group"><label class="checkbox-label"><input type="checkbox" id="corpIsSuper" /><span
                  class="checkmark"></span>スーパープレゼント</label></div>
          </div>
          <div class="form-actions" style="justify-content: flex-end;"><button type="submit" class="btn-primary"
              id="corpFormSubmit"><i class="fas fa-save"></i> 保存</button><button type="button" class="btn-primary"
              id="corpFormReset" style="margin-left: .75rem;"><i class="fas fa-rotate"></i> リセット</button></div>
        </form>
      </div>
      <div class="admin-list" id="corporateGiftsList"></div>

      <!-- Edit Corporate Gift Modal -->
      <div id="corpEditModal"
        style="position: fixed; inset: 0; background: rgba(0,0,0,.5); display: none; align-items: center; justify-content: center; z-index: 2000; padding: 1rem;">
        <div class="form-card" style="width: 100%; max-width: 680px;">
          <div class="form-header">
            <h2><i class="fas fa-pen-to-square"></i> 会社ギフトを編集</h2>
            <p>選択した会社ギフトの情報を更新します</p>
          </div>
          <form id="corpEditForm">
            <input type="hidden" id="corpEditId" />
            <div class="form-row">
              <div class="form-group"><label for="corpEditTitle">タイトル</label><input type="text" id="corpEditTitle"
                  required /></div>
              <div class="form-group"><label for="corpEditCategory">カテゴリー</label><select id="corpEditCategory">
                  <option value="food">食品・スイーツ</option>
                  <option value="fashion">ファッション</option>
                  <option value="beauty">美容・コスメ</option>
                  <option value="electronics">家電・電子機器</option>
                  <option value="books">書籍・文具</option>
                  <option value="hobby">趣味・スポーツ</option>
                  <option value="home">生活用品</option>
                  <option value="other">その他</option>
                </select></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label for="corpEditSupplyPerDay">日次供給数</label><input type="number"
                  id="corpEditSupplyPerDay" min="0" step="1" /></div>
              <div class="form-group"><label for="corpEditTotalSupply">総供給数</label><input type="number"
                  id="corpEditTotalSupply" min="0" step="1" /></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label for="corpEditStartAt">開始日時</label><input type="datetime-local"
                  id="corpEditStartAt" required /></div>
              <div class="form-group"><label for="corpEditEndAt">終了日時</label><input type="datetime-local"
                  id="corpEditEndAt" required /></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label for="corpEditDescription">説明</label><input type="text"
                  id="corpEditDescription" placeholder="任意: 詳細説明" /></div>
              <div class="form-group"><label for="corpEditImageUrl">画像URL</label>
                <div class="input-wrapper"><input type="url" id="corpEditImageUrl" style="display:none" /><input
                    type="file" id="corpEditImageFile" accept="image/*" style="margin-left: .5rem;"></div>
              </div>
              <div class="form-group"><label class="checkbox-label"><input type="checkbox" id="corpEditSuper" /><span
                    class="checkmark"></span>スーパープレゼント</label></div>
            </div>
            <div class="form-actions" style="justify-content: flex-end;"><button type="button" class="btn-primary"
                id="corpEditCancel" style="background: #999; border: none;"><i class="fas fa-times"></i>
                キャンセル</button><button type="submit" class="btn-primary" id="corpEditSave"
                style="margin-left: .75rem;"><i class="fas fa-save"></i> 更新</button></div>
          </form>
        </div>
      </div>
    </div>

    <div id="tab-users" class="admin-panel">
      <div class="form-card">
        <div class="form-header">
          <h2><i class="fas fa-user-plus"></i> ユーザー作成</h2>
          <p>管理画面から新規ユーザーを追加します</p>
        </div>
        <form id="userForm">
          <div class="form-row">
            <div class="form-group"><label for="userEmail">メール</label><input type="email" id="userEmail" required
                placeholder="user@example.com" /></div>
            <div class="form-group"><label for="userPassword">パスワード</label><input type="password" id="userPassword"
                required placeholder="8文字以上" /></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label for="userFirstName">姓</label><input type="text" id="userFirstName"
                placeholder="山田" /></div>
            <div class="form-group"><label for="userLastName">名</label><input type="text" id="userLastName"
                placeholder="太郎" /></div>
          </div>
          <div class="form-row">
            <div class="form-group"><label for="userBirth">誕生日</label><input type="date" id="userBirth" /></div>
            <div class="form-group"><label for="userAvatarUrl">アバターURL</label><div class="input-wrapper"><input type="url" id="userAvatarUrl" style="display:none" /><input type="file" id="userAvatarFile" accept="image/*" style="margin-left:.5rem;"></div></div>
          </div>
          <div class="form-actions" style="justify-content: flex-end;"><button type="submit" class="btn-primary" id="userFormSubmit"><i class="fas fa-save"></i> 追加</button></div>
        </form>
      </div>
      <div class="admin-toolbar" style="justify-content: space-between;">
        <div class="stats" id="userStats"></div>
        <div class="admin-actions"><button id="exportData" class="btn-primary"><i class="fas fa-file-export"></i>
            データ書き出し</button><button id="refreshData" class="btn-primary"><i class="fas fa-rotate"></i> 更新</button></div>
      </div>
      <div class="admin-table-wrapper">
        <table class="admin-table" id="usersTable">
          <thead>
            <tr>
              <th>ユーザー</th>
              <th>メール</th>
              <th>誕生日</th>
              <th>送信数</th>
              <th>受信数</th>
              <th>プラン</th>
              <th>権限</th>
              <th>操作</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>

      <!-- Edit User Modal -->
      <div id="userEditModal"
        style="position: fixed; inset: 0; background: rgba(0,0,0,.5); display: none; align-items: center; justify-content: center; z-index: 2000; padding: 1rem;">
        <div class="form-card" style="width: 100%; max-width: 560px;">
          <div class="form-header">
            <h2><i class="fas fa-user-edit"></i> ユーザー編集</h2>
            <p>ユーザー情報を更新します</p>
          </div>
          <form id="userEditForm">
            <input type="hidden" id="userEditId" />
            <div class="form-row">
              <div class="form-group"><label for="userEditFirst">姓</label><input type="text" id="userEditFirst" /></div>
              <div class="form-group"><label for="userEditLast">名</label><input type="text" id="userEditLast" /></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label for="userEditBirth">誕生日</label><input type="date" id="userEditBirth" />
              </div>
              <div class="form-group"><label for="userEditLevel">プラン</label><select id="userEditLevel">
                  <option value="basic">ベーシック</option>
                  <option value="premium">プレミアム</option>
                  <option value="business">ビジネス</option>
                </select></div>
            </div>
            <div class="form-row">
              <div class="form-group"><label for="userEditAvatar">アバター</label><div class="input-wrapper"><input type="url" id="userEditAvatar" style="display:none" /><input type="file" id="userEditAvatarFile" accept="image/*" style="margin-left:.5rem;"></div></div>
            </div>
            <div class="form-actions" style="justify-content: flex-end;"><button type="button" class="btn-primary"
                id="userEditCancel" style="background:#999;border:none;">キャンセル</button><button type="submit"
                class="btn-primary" id="userEditSave" style="margin-left:.75rem;">更新</button></div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="admin.js"></script>