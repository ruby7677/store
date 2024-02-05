<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>融氏手工蘿蔔糕</title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      margin: 0 auto;
      padding: 0;
      background-color: #f5f5f5;
      width: 450px;
      height: 100%;
    }
	.center-image {
	  display: block;
	  margin: 0 auto;
	  text-align: center;
	}
	h2 {
			text-align: center;
			margin: 0 auto;
            color: #333;
        }
		
	#selectedItem1,
	#selectedItem2,
	#selectedItem3 {
			font-size: 18px; /* 設置文字大小為 18 像素，根據需要調整大小 */
			font-weight: bold; /* 設置文字為粗體 */
		  }
    .form-section {
		  margin: 10px;
		  padding: 10px;
		  border-radius: 10px;
		  background-color: #ffffff;
		  box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
		}
	.form-section2 {
		  margin: 10px;
		  padding: 10px;
		  border-radius: 10px;
		  background-color: #ffffe0;
		  box-shadow: 0 0 5px rgba(0, 0, 0, 0.5);
		}
    .form-section-upper {
		  background-color: #e0f7fa;
		  border-radius: 10px 10px 10px 10px; /* 添加上方左右邊框半徑，使其更圓角 */
		}

	.form-section-lower {
		  background-color: #ffccbc;
		  border-radius: 10px 10px 10px 10px; /* 添加下方左右邊框半徑，使其更圓角 */
		}
    label {
		display: block;
		margin-bottom: 3px;
		font-size: 16px; /* 設置文字大小為 16 像素 */
		font-weight: bold; /* 設置文字為粗體 */
		}

	/* 設置選擇框內文字的大小和粗體 */
	select,
	input,
	textarea {
		width: 100%;
		padding: 8px;
		margin-bottom: 6px;
		box-sizing: border-box;
		font-size: 16px; /* 設置文字大小為 14 像素 */
		font-weight: bold; /* 設置文字為粗體 */
		}
	#addressSection {
		display: flex; /* 使用flex布局 */
		justify-content: space-between; /* 兩個欄位之間留有空隙 */
		margin-bottom: 6px; /* 與其他元素保持一定距離 */
	}

	#county {
		flex: none; /* 不使用flex比例 */
		width: calc(4 * 18px + 2em); /* 假設每個字的寬度大約是16px，再加上一些額外空間 */
		margin-right: 5px; /* 右邊留一點間隔 */
	}

	#additionalInput {
		flex-grow: 1; /* 佔據剩餘空間 */
	}
    button {
		  padding: 10px;
		  background-color: #4caf50;
		  color: #ffffff;
		  border: none;
		  border-radius: 5px;
		  cursor: pointer;
		  font-size: 22px; /* 設置文字大小為 14 像素 */
		  font-weight: bold; /* 設置文字為粗體 */
		}

    button:hover {
      background-color: #45a049;
    }
	.input-error {
	  border: 2px solid red; /* 當輸入格式錯誤時，顯示紅色邊框 */
	}
	#totalAmount {
    font-size: 18px; /* 設置文字大小 */
    font-weight: bold; /* 設置粗體 */
    color: #ff0000; /* 設置顏色，這裡使用綠色作為範例，可以根據需要調整顏色值 */
}
  </style>
<?php
session_start();
require 'D:/xampp/vendor/autoload.php';
$submitted = false;
date_default_timezone_set('Asia/Taipei');
// 表單處理部分
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $client = new \Google_Client();
    $client->setApplicationName('Order Submission');
    $client->setScopes([\Google_Service_Sheets::SPREADSHEETS]);
    $client->setAuthConfig('F:/service-account-key2.json');
    $client->setAccessType('offline');
    $service = new Google_Service_Sheets($client);

    $spreadsheetId = '10MMALrfBonchPGjb-ps6Knw7MV6lllrrKRCTeafCIuo';

    // 初始化一個陣列來儲存各個訂單物品的名稱和數量
    $orderDetails = [];
    $totalAmount = 0;
    // 遍歷所有可能的訂單物品選項（假設有3個訂單選項）
    for ($i = 1; $i <= 3; $i++) {
        if (!empty($_POST["itemSelection$i"]) && !empty($_POST["quantity$i"])) {
            // 使用explode分離商品名稱和價格
            list($itemName, $itemPrice) = explode(',', $_POST["itemSelection$i"]);
            $quantity = $_POST["quantity$i"];
            // 計算該項目總價格並加到總金額
            $totalAmount += $itemPrice * $quantity;
            // 將商品名稱和對應數量格式化為字符串，並添加到訂單詳情列表
            $orderDetails[] = "{$itemName} x {$quantity}";
        }
    }
	// 整理好所有訂單物品的詳細信息
    $orderDetailsString = implode(', ', $orderDetails);
    // 變更: 從POST請求中獲取$name和$phone，以修正未定義變數問題
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $phone = isset($_POST['phone']) ? "" . $_POST['phone'] : '';
    $storeLocation = filter_input(INPUT_POST, 'storeLocation', FILTER_SANITIZE_STRING);
    $additionalInput = filter_input(INPUT_POST, 'additionalInput', FILTER_SANITIZE_STRING);
    $deliveryDate = filter_input(INPUT_POST, 'deliveryDate', FILTER_SANITIZE_STRING);
    $deliveryTime = filter_input(INPUT_POST, 'deliveryTime', FILTER_SANITIZE_STRING);
    $notes = filter_input(INPUT_POST, 'notes', FILTER_SANITIZE_STRING);
    $socialContact = filter_input(INPUT_POST, 'facebookline', FILTER_SANITIZE_STRING);
	$facebooklineid = filter_input(INPUT_POST, 'facebooklineid', FILTER_SANITIZE_STRING);
	$pay = filter_input(INPUT_POST, 'pay', FILTER_SANITIZE_STRING);
	$county = isset($_POST['county']) ? $_POST['county'] : '';
	$additionalInput = isset($_POST['additionalInput']) ? $_POST['additionalInput'] : '';
	// 合併縣市值和地址或門市值
	$fullAddress = $county . '-' . $additionalInput;
	// 提交資料到GOOGLE試算表
	$data = [
		[
        date("Y-m-d H:i:s"), $name, $phone, $storeLocation,
        $fullAddress, $deliveryDate, $deliveryTime,
        $notes, $orderDetailsString, $totalAmount, $socialContact, $facebooklineid,
		$pay
		]
	];
	// 設定API參數
	$body = new Google_Service_Sheets_ValueRange([
		'values' => $data
	]);
    $params = [
        'valueInputOption' => 'RAW'
    ];
    // 定義所要插入數據的範圍，例如：訂單工作表名稱!起始單元格:終止單元格
    $range = 'Sheet1!A2';
	// 嘗試發送數據到Google Sheets
    try {
    $service->spreadsheets_values->append($spreadsheetId, $range, $body, $params);
    // 設置成功訊息到session，稍後用JavaScript彈出
    // 在客戶端跳轉前通知用戶
    echo "<script type='text/javascript'>alert('訂單提交成功!'); window.location.href='" . $_SERVER['PHP_SELF'] . "';</script>";
    exit;
	} 
	catch (Exception $e) {
		// 出錯時設定錯誤消息
		$_SESSION['error_message'] = "訂單提交失敗: " . $e->getMessage();
		// 將錯誤訊息以JavaScript彈出視窗的形式顯示，然後停留在當前頁面
		echo "<script type='text/javascript'>alert('訂單提交失敗: " . $e->getMessage() . "');</script>";
	}
}
	// 如果存在成功或錯誤消息，顯示之
	if (isset($_SESSION['success_message'])) {
		echo "<p>" . $_SESSION['success_message'] . "</p>";
		unset($_SESSION['success_message']);
	} elseif (isset($_SESSION['error_message'])) {
		echo "<p>" . $_SESSION['error_message'] . "</p>";
		unset($_SESSION['error_message']);
	}

?>

</head>

<body>
<br>
<img class="mb-4 center-image" src="img/IMG_1439.JPG" alt="" width="420" height="120">
<h2>訂單系統</h2>
	<form action="" method="post" class="form-section2" id="orderForm" onsubmit="return validateAndSubmitForm()">
    <div class="form-section form-section-upper">
      <!-- 上區塊內容 -->
      <div class="form-section">
        <!-- 第一組訂單項目 -->
        <span id="selectedItem1">原味蘿蔔糕, $200</span>
        <input type="hidden" id="itemSelection1" name="itemSelection1" value="原味蘿蔔糕,200">
        <label for="quantity1">數量:</label>
        <input type="number" id="quantity1" name="quantity1" oninput="calculateTotal()">
        <!-- 第二組訂單項目 -->
        <span id="selectedItem2">芋頭粿, $350</span>
        <input type="hidden" id="itemSelection2" name="itemSelection2" value="芋頭粿,350">
        <label for="quantity2">數量:</label>
        <input type="number" id="quantity2" name="quantity2" oninput="calculateTotal()">
        <!-- 第三組訂單項目 -->
        <span id="selectedItem3">港式蘿蔔糕, $350</span>
        <input type="hidden" id="itemSelection3" name="itemSelection3" value="港式蘿蔔糕,350">
        <label for="quantity3">數量:</label>
        <input type="number" id="quantity3" name="quantity3" oninput="calculateTotal()">
        <!-- 總金額 -->
        <label for="totalAmount">總金額:</label>
        <div id="totalAmount">$0</div>
      </div>
    </div>

    <div class="form-section form-section-lower">
      <!-- 下區塊內容 -->
      <div class="form-section">
		<label for="name">姓名：</label>
		<input type="text" id="name" name="name" required>
		<label for="phone">行動電話：</label>
		<input type="tel" id="phone" name="phone" placeholder="09XXXXXXXX" oninput="validatePhoneNumber()" required>
		<span id="phoneError" style="color: red; display: none;">輸入格式錯誤，09XXXXXXXX</span>
        <!-- 宅配方式 -->
        <label for="storeLocation">請選擇宅配方式：</label>
		<select id="storeLocation" name="storeLocation" onchange="showAdditionalInput()">
			<option value="" selected disabled>請選擇宅配方式</option>
			<option value="宅配到府">宅配到府</option>
			<option value="7-11門市">7-11門市</option>
		</select>

		<!-- 新增台灣縣市下拉菜單 -->
		<div id="addressSection" style="display: none;"> <!-- 初始設為隱藏 -->
			<select id="county" name="county" required>
				<option value="">選擇縣市</option>
				<option value="基隆市">基隆市</option>
				<option value="台北市">台北市</option>
				<option value="新北市">新北市</option>
				<option value="桃園市">桃園市</option>
				<option value="新竹市">新竹市</option>
				<option value="新竹縣">新竹縣</option>
				<option value="苗栗縣">苗栗縣</option>
				<option value="台中市">台中市</option>
				<option value="彰化縣">彰化縣</option>
				<option value="南投縣">南投縣</option>
				<option value="雲林縣">雲林縣</option>
				<option value="嘉義市">嘉義市</option>
				<option value="嘉義縣">嘉義縣</option>
				<option value="台南市">台南市</option>
				<option value="高雄市">高雄市</option>
				<option value="屏東縣">屏東縣</option>
				<option value="台東縣">台東縣</option>
				<option value="花蓮縣">花蓮縣</option>
				<option value="宜蘭縣">宜蘭縣</option>
				<option value="澎湖縣">澎湖縣</option>
				<option value="金門縣">金門縣</option>
				<option value="連江縣">連江縣</option>
			</select>
			<input type="text" id="additionalInput" name="additionalInput"
				   placeholder="地址或門市名稱" required>
		</div>
        <label for="deliveryDate">希望到貨日：</label>
        <input type="date" id="deliveryDate" name="deliveryDate" required>
		<!-- 時段 -->
        <label for="deliveryTime">時段：</label>
        <select id="deliveryTime" name="deliveryTime">
            <option value="上午">上午</option>
            <option value="下午">下午</option>
        </select>
		<label for="facebookline">透過什麼聯繫賣家：</label>
		<select id="facebookline" name="facebookline" required>
		  <option value="臉書粉絲團">臉書粉絲團</option>
		  <option value="Line官方">Line官方</option>
		</select>
		<input type="text" id="facebooklineid" name="facebooklineid" placeholder="務必留下您的LINE或臉書名稱以便賣家聯繫" required>
		<label for="pay">請選擇付款方式：</label>
		<select id="pay" name="pay" required>
		  <option value="貨到付款">貨到付款</option>
		  <option value="轉帳匯款">轉帳匯款(請用FB或LINE私訊客服索取轉帳帳號)</option>
		</select>
        <label for="notes">備註:</label>
		<textarea id="notes" name="notes"></textarea>

        <button id="submit-button" type="submit">提交訂單</button>
      </div>
    </div>
  </form>

  <script>
  function calculateTotal() {
    let total = 0;

    for (let i = 1; i <= 3; i++) {
      const itemInput = document.getElementById(`itemSelection${i}`).value;
      const quantityInput = document.getElementById(`quantity${i}`).value;

      if (itemInput && quantityInput) {
        const [itemName, itemPriceStr] = itemInput.split(',');
        const itemPrice = parseFloat(itemPriceStr);
        const quantity = parseFloat(quantityInput) || 0;
        total += itemPrice * quantity;
      }
    }

    document.getElementById('totalAmount').textContent = `$${total.toFixed(0)}`;
  }

  function showAdditionalInput() {
    var storeLocation = document.getElementById('storeLocation').value;
    var addressSection = document.getElementById('addressSection');

    if (storeLocation === '宅配到府' || storeLocation === '7-11門市') {
        addressSection.style.display = 'flex'; // 顯示地址欄位，使用flex布局以達到左右兩格
    } else {
        addressSection.style.display = 'none'; // 隱藏地址欄位
    }
}

  function validateForm() {
    // 驗證是否至少填寫一組訂單數量
    const quantity1 = parseFloat(document.getElementById('quantity1').value) || 0;
    const quantity2 = parseFloat(document.getElementById('quantity2').value) || 0;
    const quantity3 = parseFloat(document.getElementById('quantity3').value) || 0;

    // 判斷是否有至少一組訂單數量大於 0
    if (!(quantity1 > 0 || quantity2 > 0 || quantity3 > 0)) {
      alert('至少需要填寫一組訂單數量。');
      return false; // 防止表單提交
    }
	    
	var selectedSocialContact = document.getElementById('facebooklineid').value;
      if (!selectedSocialContact) {
        alert('請選擇付款方式。');
        return false;
      }
		calculateTotal();
		return true; // 允许表单提交
	}

	function validateAndSubmitForm() {
      // 執行表單驗證
      const isValid = validateForm();

      // 如果驗證通過，禁用提交按鈕來防止重複提交
      if (isValid) {
			document.getElementById('submit-button').disabled = true;
		} else {
			// 如果表單驗證不通過，邏輯保持按鈕可用以允許用戶修正錯誤
			// 不需要執行任何動作，因為按鈕本來就應該是可用的
		}
		// 返回驗證結果
		return isValid;
    }	  
				  
	function updateSocialContact() {
		// 新增函数用于更新社交联系方式
		var socialContactInput = document.getElementById('socialContact');
		var selectedSocialContact = document.getElementById('facebookline').value;
		if (socialContactInput && selectedSocialContact) {
		  socialContactInput.value = selectedSocialContact;
		}
	}

	function setMinDate() {
		var today = new Date();
		var minDate = new Date(today);
		minDate.setDate(today.getDate() + 1); // 设置最小日期为今天加上 1 天

		var formattedMinDate = minDate.toISOString().split('T')[0];
		var dateInput = document.getElementById('deliveryDate');
		if (dateInput) {
		  dateInput.min = formattedMinDate;
		}
	}
	function validatePhoneNumber() {
	  var phoneInput = document.getElementById('phone');
	  var phoneError = document.getElementById('phoneError');
	  var phoneValue = phoneInput.value;

	  // 使用正則表達式檢查電話號碼是否為09開頭且長度為10的數字
	  if (/^09\d{8}$/.test(phoneValue)) {
		// 如果匹配，移除錯誤樣式並隱藏錯誤訊息
		phoneInput.classList.remove('input-error');
		phoneError.style.display = 'none';
	  } else {
		// 如果不匹配，添加錯誤樣式並顯示錯誤訊息
		phoneInput.classList.add('input-error');
		phoneError.style.display = 'block';
	  }
	}

	// 当页面加载完成后，立即执行这个函数以设置最小日期
	  document.addEventListener('DOMContentLoaded', function () {
		setMinDate();
		// 更新社交联系方式
		updateSocialContact();
		// 添加对宅配方式变化的监听
		document.getElementById('storeLocation').addEventListener('change', showAdditionalInput);
	  });
  
</script>
</body>

</html>