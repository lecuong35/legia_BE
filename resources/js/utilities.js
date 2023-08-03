const VND = new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
  });

function changePrice(oldPrice, id) {
    ele = document.getElementById(id);
    ele.innerHTML = VND.format(oldPrice);
}
