<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice</title>
    <script src="../js//utilities.js"></script>
</head>
<body>
    <style>
        @font-face {
            font-family: 'DejaVu Sans, sans-serif';
            src: url(../css/dejavu-sans.book.ttf);
        }
         html, body {
            font-family: 'DejaVu Sans, sans-serif';
         }
         table {
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid black;
            padding: 8px;
        }
        img {
            width: 50px;
        }
        .image{
            position: absolute;
            top: 20px;
            left: 20px;
        }
        .title{
            text-align: center;
        }
    </style>
   <div>
        <div class="title">
            <div class="image">
                <img src="https://uploads.turbologo.com/uploads/design/hq_preview_image/87878/draw_svg20210814-32701-t5984m.svg.png" alt="logo">
            </div>
            <h2>
                CÔNG TY TNHH PHỤ TÙNG Ô TÔ LÊ GIA
            </h2>
        </div>
        <div>
           <div>
                <p>
                    Văn phòng: Số 17 Ngách 75 Ngõ 184 Đê Trần Khát Chân - Hai Bà Trưng - Hà Nội
                </p>
                <p>Điện thoại: 0982277198</p>
                <p>
                    Email: cuonglemanh352001@gmail.com
                </p>
           </div>
        </div>
   </div>

   <div>
        <h2>
            PHIẾU XUẤT KHO
        </h2>
        <p>
            Ngày xuất: {{Carbon\Carbon::now('Asia/Ho_Chi_Minh')->format('d-m-Y H:i:s')}}
        </p>
   </div>

   <div>
        <div>
           <div>
                @if ($bill['user'] != null)
                    <p>Khách hàng: {{$bill['user']['name']}}</p>
                @else
                    <p>Khách hàng: Khách lẻ</p>
                @endif
           </div>
           <div>
                Địa chỉ: {{$bill['address']}}
           </div>
        </div>
        <div>
            <p>Điện thoại: {{$bill['customer_phone']}}</p>
        </div>
   </div>

   <div>
    <table>
        <thead>
            <th>TT</th>
            <th>Tên sản phẩm</th>
            <th>Giá sản phẩm</th>
            <th>Số lượng</th>
            <th>Thành giá</th>
        </thead>
        <tbody>
            @foreach($bill['cart_items'] as $key => $item)
                <tr>
                    <td>{{$key + 1}}</td>
                    <td>{{$bill['products'][$key]['name']}}</td>
                    <td id="item{{$key}}" onload="changePrice($bill['products'][$key]['price']), 'item{{$key}}'">
                        <!-- {{$bill['products'][$key]['price']}} -->
                        {{number_format($bill['products'][$key]['price'], 0, ',', '.') . ' ₫'}}
                    </td>
                    <td>{{$item['quantity']}}</td>
                    <td id="subtotal{{$key}}" onload="changePrice($bill['products'][$key]['price'] * $item['quantity']), 'subtotal{{$key}}'">
                        <!-- {{ $bill['products'][$key]['price'] * $item['quantity'] }} -->
                       {{ number_format($bill['products'][$key]['price'] * $item['quantity'], 0, ',', '.') . ' ₫'}}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <h4>Thành tiền: <span id="total" onload="changePrice($bill['total']), 'total'">
         <!-- {{ $bill['total'] }} VNĐ -->
         {{number_format( $bill['total'], 0, ',', '.') . ' ₫'}}
    </span></h4>
   </div>

   <div>
    <p style="font-size: medium;">Xin quý khách lưu ý: <span>Hàng được đổi trả trong vòng 7 ngày kể từ ngày xuất kho với
        điều kiện hàng vẫn còn nguyên trạng, không lắp ráp, trầy xước.
    </span></p>
   </div>

   <div>
    <p>Người lập phiếu</p>
    <p>Phụ tùng ô tô Lê Gia</p>
   </div>
   <div>
        <p>Khách hàng</p>
   </div>
</body>
</html>
