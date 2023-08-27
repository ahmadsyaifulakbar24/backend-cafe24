<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Delivery Order</title>
    <style>
        .left {
            float: left;
        }
        .right {
            width: 300px;
            float: right;
            margin-top: 10px;
            margin-right: 20px;
        }
        .line {
            width: 100%; 
            height: 2px; 
            margin-top: -10px;
            margin-bottom: 10px;
            background-color: black; 
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
        table {
            border-collapse: collapse;
            width: 100%; /* Lebar tabel */
        }

        /* Atur tampilan baris */
        tr {
            border-top: 1px solid #000; /* Border atas */
            border-bottom: 1px solid #000; /* Border bawah */
        }

        /* Atur tampilan sel */
        td {
            padding: 8px; /* Ruang dalam sel */
            text-align: center; /* Aliran teks kiri */
        }
        .no {
            width: 60px;
        }
        .banyaknya {
            width: 80px;
        }
        .nama-item {
            width: 100%;
            text-align: left;
            padding: 0 20px;
        }

    </style>
</head>
<body>
    <div>
        <div class="left">
                <p style="font-size: 1.5rem;">Klinik Cafe24 Bandung</p>
                <p style="font-size: 1.1rem; margin-top: -10px;">
                    Jl. Tubagus Ismail Raya No. 1A<br>
                    Sekeloa, Coblong, Kota Bandung<br>
                    Jawa Barat, 40134<br>
                    62818567894
                </p>
        </div>
        <div class="right">
                <p style="font-size: 1.1rem;">Tanggal: 21 Agustus 2023</p>
                <p style="font-size: 1.1rem; margin-top: -10px;">
                    Kepada Yth.
                </p>
                <div class="line"></div>
                <p style="font-size: 1.2rem; font-weight: bold; margin-top: -10px;">Contoh</p>
                <p style="font-size: 1.1rem; margin-top: -10px;">
                    Jl. Bangreng No.9, Turangga, Kec. <br>
                    Lengkong, Kota Bandung, Jawa Barat 40264 <br><br>
                    082130518825
                </p>
                <div class="line"></div>
        </div>
    </div>
    <div class="clearfix"></div>
    <p style="font-size: 1.1rem;">No. Nota INV/KC24/230821-00240</p>
    <table style="margin-bottom: 10px;">
        <tr>
            <th class="no">NO</th>
            <th class="banyaknya">BANYAKNYA</th>
            <th class="nama-item">NAMA ITEM</th>
        </tr>
        <tr>
            <td class="no">1</th>
            <td class="banyaknya">6</th>
            <td class="nama-item">Denali Syrup:Caramel</th>
        </tr>
        <tr>
            <td class="no">2</th>
            <td class="banyaknya">6</th>
            <td class="nama-item">Denali Syrup:Caramel</th>
        </tr>
    </table>
    <div class="clearfix"></div>
    <p style="font-size: 1.1rem; text-align: center; margin-top: -20px;">[Printed by e-Nota]</p>
    <p style="font-size: 1.1rem; margin-top: -20px;">Total qty: 12</p>
    <div>
        <div class="left" style="width: 200px; margin-left: 50px;">
            <p>Tanda Terima</p>
        </div>
        <div class="right" style="width: 100px; margin-right: 60px;">
            <p style="text-align: center;">Hormat kami</p>
            <div style="position: relative;">
                <img style="position: absolute; right: -10%; width: 150px;" src="https://th.bing.com/th/id/R.0da724c2224f1aca7742b1ae379af13f?rik=nKsvrdvLEWSS3Q&riu=http%3a%2f%2f4.bp.blogspot.com%2f-PFN93k5bW6I%2fTa_wbSg_r1I%2fAAAAAAAAABw%2fqPZlR2Kmqx8%2fw1200-h630-p-k-no-nu%2fsignature.jpg&ehk=4rK6tBamoAcIduOBmCoTjHscTfFsVGBOu7yjDY0MXvk%3d&risl=&pid=ImgRaw&r=0" />
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div style="margin: 80px 0;"></div>
    <p style="font-size: 1.1rem; text-align: center; margin-top: -20px;">***Thank You***</p>
    <p style="font-size: 1.1rem; text-align: center; margin-top: -20px;">21/08/2023 12:17:41</p>
    <br>
    <p style="font-size: 1.1rem; text-align: center; margin-top: -20px;">[BCA a/ Nasharudin 3720-0150-04]</p>
</body>
</html>