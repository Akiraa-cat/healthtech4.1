
document.addEventListener("DOMContentLoaded", function() {
    let savedTheme = localStorage.getItem("themeColor");
    if (savedTheme) {
        let theme = JSON.parse(savedTheme);
        document.documentElement.style.setProperty('--main-color', theme.base);
        document.documentElement.style.setProperty('--hover-color', theme.hover);
        document.documentElement.style.setProperty('--text-color', theme.text);
    }
});


const categoryData = 
{
  "sleep": {
    "title": "Seberapa baik kualitas tidurmu? 🛌",
    "desc": "Sering nyenyak atau malah susah tidur? \nCek kebiasaan tidurmu dan temukan solusinya!",
    "questions": [
      {
        "question": "Berapa jam rata-rata Anda tidur setiap malam?",
        "options": [
          "8-9 jam",
          "6-7 jam",
          "Kurang Dari 4 jam",
          "4-5 jam",
          "Lebih dari 9 jam"
        ],
        "scores": [-2, -1, 6, 3, 5],
        "goodTips": "Tidur yang cukup adalah ideal untuk kesehatan jangka panjang! 😊",
        "badTips": "Tidur yang kurang dapat berdampak buruk bagi kesehatan. Cobalah untuk tidur lebih awal! 😴",
        "neutralTips": "Tidur 6-7 jam cukup baik, tapi bisa lebih optimal dengan tidur yang lebih teratur. ⏳"
      },
      {
        "question": "Apa yang biasanya Anda lakukan sebelum tidur?",
        "options": [
          "Meditasi atau relaksasi",
          "Makan berat",
          "Nonton TV",
          "Baca buku",
          "Main Gadget"
        ],
        "scores": [-2, -1, 3, 5, 6],
        "goodTips": "Meditasi atau relaksasi sebelum tidur membantu tidur lebih nyenyak! 🌙",
        "badTips": "Melakukan hal lain selain menenangkan diri sebelum tidur bisa mengganggu kualitas tidur. 📵",
        "neutralTips": "Rutinitas sebelum tidur penting, coba kurangi aktivitas yang menghambat tidur. 🛏️"
      },
      {
        "question": "Seberapa sering Anda mengalami kesulitan tidur atau insomnia?",
        "options": [
          "Tidak pernah",
          "Jarang",
          "Kadang-kadang",
          "Sering",
          "Setiap malam"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Tidak mengalami kesulitan tidur adalah tanda kesehatan yang baik! 😊",
        "badTips": "Jika Anda mengalami kesulitan tidur setiap malam, pertimbangkan untuk berkonsultasi dengan dokter. 😴",
        "neutralTips": "Kadang mengalami kesulitan tidur itu normal, tapi coba cari tahu penyebabnya. ⏳"
      },
      {
        "question": "Seberapa segar tubuh Anda saat bangun tidur?",
        "options": [
          "Selalu segar",
          "Sering segar",
          "Kadang-kadang segar",
          "Jarang segar",
          "Tidak pernah segar"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Selalu merasa segar saat bangun tidur adalah tanda tidur yang berkualitas! 😊",
        "badTips": "Tidak merasa segar bisa jadi tanda kurang tidur atau kualitas tidur yang buruk. Cobalah untuk memperbaiki rutinitas tidur Anda. 😴",
        "neutralTips": "Kadang merasa segar itu normal, tapi coba perbaiki kebiasaan tidur Anda. ⏳"
      },
      {
        "question": "Apakah Anda memiliki jadwal tidur yang konsisten setiap hari?",
        "options": [
          "Selalu",
          "Sering",
          "Kadang-kadang",
          "Jarang",
          "Tidak pernah"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Rutinitas tidur yang konsisten sangat baik untuk kualitas tidur! 😊",
        "badTips": "Tidak memiliki rutinitas bisa mengganggu pola tidur Anda. Cobalah untuk menetapkan waktu tidur yang tetap. 😴",
        "neutralTips": "Kadang memiliki rutinitas itu baik, tapi lebih baik jika konsisten. ⏳"
      },
      {
        "question": "Apa yang biasanya membangunkan Anda di tengah malam?",
        "options": [
          "Tidak ada",
          "Suara bising",
          "Kebutuhan untuk ke toilet",
          "Keringat berlebih",
          "Mimpi buruk"
        ],
        "scores": [6, -1, -1, -1, -2],
        "goodTips": "Tidak terbangun di malam hari adalah tanda tidur yang berkualitas! 😊",
        "badTips": "Jika Anda terbangun pada malam hari, coba cari tahu penyebabnya dan jika terlalu sering coba konsultasikan ke psikolog. 😴",
        "neutralTips": "Kadang terbangun itu normal, tapi coba cari tahu penyebabnya. ⏳"
      },
      {
        "question": "Seberapa sering Anda tidur siang?",
        "options": [
          "Setiap hari",
          "Sering",
          "Kadang-kadang",
          "Jarang",
          "Hampir tidak pernah"
        ],
        "scores": [5, 3, 1, -1, -2],
        "goodTips": "Tidur siang yang cukup dapat meningkatkan energi dan fokus! 😊",
        "badTips": "Tidur siang yang terlalu lama bisa mengganggu tidur malam Anda. Cobalah untuk membatasi durasinya. 😴",
        "neutralTips": "Tidur siang sesekali baik, tapi jangan terlalu sering. ⏳"
      },
      {
        "question": "Bagaimana kondisi kamar tidur Anda (gelap, tenang, nyaman)?",
        "options": [
          "Sangat nyaman",
          "Cukup nyaman",
          "Biasa saja",
          "Kurang nyaman",
          "Sangat tidak nyaman"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Kondisi kamar yang nyaman sangat penting untuk tidur yang berkualitas! 😊",
        "badTips": "Kamar yang tidak nyaman bisa mengganggu tidur Anda. Cobalah untuk menyesuaikan kondisi kamar. 😴",
        "neutralTips": "Kondisi kamar yang biasa saja mungkin tidak ideal, coba cari cara untuk membuatnya lebih nyaman. 🛏️"
      },
      {
        "question": "Apakah Anda menggunakan gadget sebelum tidur?",
        "options": [
          "Tidak pernah",
          "Jarang",
          "Kadang-kadang",
          "Sering",
          "Setiap malam"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Tidak menggunakan gadget sebelum tidur sangat baik untuk kualitas tidur! 😊",
        "badTips": "Menggunakan gadget sebelum tidur bisa mengganggu kualitas tidur. Cobalah untuk menghindarinya. 📵",
        "neutralTips": "Kadang menggunakan gadget itu wajar, tapi lebih baik jika tidak dilakukan menjelang tidur. ⏳"
      },
      {
        "question": "Jika sulit tidur, apa yang biasanya Anda lakukan?",
        "options": [
          "Meditasi atau pernapasan dalam",
          "Mendengarkan Musik Santai",
          "Menonton TV",
          "Main HP",
          "Minum Kopi"
        ],
        "scores": [-2, -1, 3, 5, 6],
        "goodTips": "Meditasi atau pernapasan membantu menenangkan pikiran sebelum tidur. Pertahankan kebiasaan baik ini! 😊",
        "badTips": "Minum kopi atau main HP justru bisa membuat Anda semakin sulit tidur. Cobalah menghindari kebiasaan ini sebelum tidur. 📵",
        "neutralTips": "Mendengarkan musik bisa membantu, tapi pastikan itu musik yang menenangkan. 🎵"
      },
      {
        "question": "Apakah Anda pernah tertidur tanpa sengaja saat beraktivitas (kerja/sekolah)?",
        "options": [
          "Tidak pernah",
          "Jarang",
          "Kadang-kadang",
          "Sering",
          "Setiap hari"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Tidak tertidur saat beraktivitas adalah tanda tidur yang berkualitas! 😊",
        "badTips": "Sering tertidur saat beraktivitas bisa jadi tanda kurang tidur. Cobalah untuk memperbaiki pola tidur Anda. 😴",
        "neutralTips": "Kadang tertidur itu normal, tapi coba perbaiki kebiasaan tidur Anda. ⏳"
      },
      {
        "question": "Seberapa sering Anda merasa mengantuk di siang hari?",
        "options": [
          "Tidak pernah",
          "Jarang",
          "Kadang-kadang",
          "Sering",
          "Setiap hari"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Tidak merasa mengantuk di siang hari adalah tanda tidur yang berkualitas! 😊",
        "badTips": "Sering merasa mengantuk bisa jadi tanda kurang tidur. Cobalah untuk memperbaiki pola tidur Anda. 😴",
        "neutralTips": "Kadang merasa mengantuk itu normal, tapi coba perbaiki kebiasaan tidur Anda. ⏳"
      },
      {
        "question": "Apakah Anda mendengkur saat tidur?",
        "options": [
          "Tidak pernah",
          "Jarang",
          "Kadang-kadang",
          "Sering",
          "Setiap malam"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Bagus! Tidak mendengkur berarti saluran pernapasan Anda lancar. 😊",
        "badTips": "Jika mendengkur setiap malam, bisa jadi ada gangguan tidur seperti sleep apnea. Coba konsultasikan ke dokter. 😴",
        "neutralTips": "Kadang mendengkur itu wajar, tapi coba perhatikan pola tidur Anda. 🛏️"
      },
      {
        "question": "Seberapa sering Anda terbangun lebih awal dari yang diinginkan?",
        "options": [
          "Tidak pernah",
          "Jarang",
          "Kadang-kadang",
          "Sering",
          "Setiap hari"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Tidak terbangun lebih awal adalah tanda tidur yang berkualitas! 😊",
        "badTips": "Sering terbangun lebih awal bisa jadi tanda gangguan tidur. Cobalah untuk memperbaiki pola tidur Anda. 😴",
        "neutralTips": "Kadang terbangun lebih awal itu normal, tapi coba perbaiki kebiasaan tidur Anda. ⏳"
      },
      {
        "question": "Apa yang paling sering mengganggu tidur Anda?",
        "options": [
          "Tidak ada",
          "Suara bising",
          "Kebutuhan untuk ke toilet",
          "Keringat berlebih",
          "Mimpi buruk"
        ],
        "scores": [6, -1, -1, -1, -2],
        "goodTips": "Tidak ada gangguan tidur adalah tanda tidur yang berkualitas! 😊",
        "badTips": "Jika Anda terbangun karena mimpi buruk, pertimbangkan untuk berbicara dengan profesional. 😴",
        "neutralTips": "Kadang terbangun itu normal, tapi coba cari tahu penyebabnya. ⏳"
      },
      {
        "question": "Apakah Anda mengalami mimpi buruk atau sleep paralysis?",
        "options": [
          "Tidak pernah",
          "Jarang",
          "Kadang-kadang",
          "Sering",
          "Setiap malam"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Tidak mengalami mimpi buruk adalah tanda tidur yang berkualitas! 😊",
        "badTips": "Jika Anda mengalami mimpi buruk setiap malam, pertimbangkan untuk berbicara dengan profesional. 😴",
        "neutralTips": "Kadang mengalami mimpi buruk itu normal, tapi coba cari tahu penyebabnya. ⏳"
      },
      {
        "question": "Seberapa sering Anda merasa lelah meskipun tidur cukup?",
        "options": [
          "Tidak pernah",
          "Jarang",
          "Kadang-kadang",
          "Sering",
          "Setiap hari"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Tidak merasa lelah meskipun tidur cukup adalah tanda tidur yang berkualitas! 😊",
        "badTips": "Sering merasa lelah bisa jadi tanda kualitas tidur yang buruk. Cobalah untuk memperbaiki pola tidur Anda. 😴",
        "neutralTips": "Kadang merasa lelah itu normal, tapi coba perbaiki kebiasaan tidur Anda. ⏳"
      },
      {
        "question": "Bagaimana rutinitas pagi Anda setelah bangun tidur?",
        "options": [
          "Sangat teratur",
          "Cukup teratur",
          "Biasa saja",
          "Kurang teratur",
          "Sangat tidak teratur"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Rutinitas pagi yang baik membantu memulai hari dengan positif! 😊",
        "badTips": "Rutinitas yang tidak teratur bisa membuat Anda merasa tidak siap untuk hari. Cobalah untuk membuat rutinitas yang lebih baik. 😴",
        "neutralTips": "Rutinitas yang biasa saja mungkin tidak ideal, coba cari cara untuk membuatnya lebih baik. ⏳"
      },
      {
        "question": "Apakah Anda pernah mencoba teknik relaksasi sebelum tidur?",
        "options": [
          "Selalu",
          "Sering",
          "Kadang-kadang",
          "Jarang",
          "Tidak pernah"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Teknik relaksasi sebelum tidur sangat baik untuk kualitas tidur! 😊",
        "badTips": "Tidak pernah mencoba teknik relaksasi bisa membuat tidur Anda kurang berkualitas. Cobalah untuk mencobanya. 😴",
        "neutralTips": "Kadang mencoba teknik relaksasi itu baik, tapi lebih baik jika dilakukan secara rutin. ⏳"
      },
      {
        "question": "Jika mengalami gangguan tidur, apa langkah pertama yang Anda ambil?",
        "options": [
          "Berbicara dengan dokter",
          "Mencoba teknik relaksasi",
          "Mengubah rutinitas tidur",
          "Mengabaikannya",
          "Mencari informasi di internet"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Berbicara dengan dokter adalah langkah yang baik untuk mengatasi gangguan tidur! 😊",
        "badTips": "Mengabaikan gangguan tidur bisa memperburuk masalah. Cobalah untuk mencari solusi. 😴",
        "neutralTips": "Mencari informasi bisa membantu, tapi lebih baik jika berkonsultasi dengan profesional. ⏳"
      }
    ]
  },
  "stress": {
    "title": "Bagaimana kesehatan mentalmu? 🧘‍♂️",
    "desc": "Apakah stres dan emosimu sudah terkendali? \nTes ini bantu kamu mengenali dan menjaga kesehatan mentalmu!",
    "questions": [
      {
        "question": "Bagaimana perasaan Anda secara umum dalam seminggu terakhir?",
        "options": [
          "Sangat bahagia",
          "Cukup bahagia",
          "Biasa saja",
          "Cukup stres",
          "Sangat stres"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Senang mendengar bahwa Anda merasa bahagia! Tetap jaga keseimbangan hidup. 😊",
        "badTips": "Jika terus merasa stres, coba cari waktu untuk diri sendiri atau berbicara dengan seseorang yang dipercaya. ❤️",
        "neutralTips": "Perasaan naik turun itu normal, pastikan Anda tetap menjaga kesehatan mental. 🌿"
      },
      {
        "question": "Apa yang biasanya Anda lakukan saat merasa stres?",
        "options": [
          "Olahraga",
          "Meditasi",
          "Makan berlebihan",
          "Menonton TV tanpa henti",
          "Menghindari masalah"
        ],
        "scores": [6, 5, -1, -1, -2],
        "goodTips": "Olahraga sangat baik untuk mengurangi stres. Teruskan kebiasaan positif ini! 🏃‍♂️",
        "badTips": "Menghindari masalah atau makan berlebihan tidak akan menyelesaikan stres. Cobalah cari cara yang lebih sehat untuk mengatasinya. 🍔➡️🚫",
        "neutralTips": "Menonton TV bisa mengalihkan pikiran sejenak, tapi pastikan tetap menyelesaikan masalah yang ada. 📺"
      },
      {
        "question": "Seberapa sering Anda merasa cemas tanpa alasan jelas?",
        "options": [
          "Tidak pernah",
          "Jarang",
          "Kadang-kadang",
          "Sering",
          "Setiap hari"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Tidak merasa cemas adalah tanda mental yang sehat! 😊",
        "badTips": "Jika Anda merasa cemas setiap hari, pertimbangkan untuk berbicara dengan profesional. 😟",
        "neutralTips": "Kadang merasa cemas itu normal, tapi coba lakukan relaksasi. 🌿"
      },
      {
        "question": "Bagaimana kualitas hubungan sosial Anda dengan teman atau keluarga?",
        "options": [
          "Sangat baik",
          "Cukup baik",
          "Biasa saja",
          "Kurang baik",
          "Sangat buruk"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Hubungan sosial yang baik sangat penting untuk kesehatan mental! 😊",
        "badTips": "Hubungan yang buruk bisa membuat Anda merasa terisolasi. Cobalah untuk memperbaiki hubungan Anda. ❤️",
        "neutralTips": "Hubungan yang biasa saja mungkin tidak ideal, coba cari cara untuk membuatnya lebih baik. 🌿"
      },
      {
        "question": "Seberapa sering Anda mengalami perubahan suasana hati secara tiba-tiba?",
        "options": [
          "Tidak pernah",
          "Jarang",
          "Kadang-kadang",
          "Sering",
          "Setiap hari"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Tidak mengalami perubahan suasana hati yang tiba-tiba adalah tanda mental yang stabil! 😊",
        "badTips": "Jika Anda mengalami perubahan suasana hati setiap hari, pertimbangkan untuk berbicara dengan profesional. 😟",
        "neutralTips": "Kadang mengalami perubahan suasana hati itu normal, tapi coba cari tahu penyebabnya. 🌿"
      },
      {
        "question": "Jika mengalami masalah, kepada siapa Anda biasanya bercerita?",
        "options": [
          "Teman dekat",
          "Keluarga",
          "Profesional",
          "Sendiri",
          "Tidak pernah bercerita"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Berbicara dengan orang terdekat sangat baik untuk kesehatan mental! 😊",
        "badTips": "Tidak pernah bercerita bisa membuat Anda merasa tertekan. Cobalah untuk berbicara dengan seseorang yang Anda percayai. ❤️",
        "neutralTips": "Kadang berbicara itu baik, tapi lebih baik jika dilakukan secara rutin. 🗣️"
      },
      {
        "question": "Seberapa sering Anda merasa kewalahan dengan tugas atau tanggung jawab?",
        "options": [
          "Tidak pernah",
          "Jarang",
          "Kadang-kadang",
          "Sering",
          "Setiap hari"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Tidak merasa kewalahan adalah tanda manajemen waktu yang baik! 😊",
        "badTips": "Jika Anda merasa kewalahan setiap hari, pertimbangkan untuk mengatur ulang prioritas Anda. 😟",
        "neutralTips": "Kadang merasa kewalahan itu normal, tapi coba cari cara untuk mengelola tugas Anda. 🌿"
      },
      {
        "question": "Apa cara utama Anda mengatasi tekanan atau kecemasan?",
        "options": [
          "Olahraga",
          "Meditasi",
          "Makan",
          "Tidur",
          "Menghindari masalah"
        ],
        "scores": [6, 5, -1, -1, -2],
        "goodTips": "Olahraga adalah cara yang sangat baik untuk mengatasi tekanan! 😊",
        "badTips": "Menghindari masalah tidak akan menyelesaikan tekanan. Cobalah untuk mencari solusi. 🍔➡️🚫",
        "neutralTips": "Kadang tidur bisa membantu, tapi lebih baik jika Anda menyelesaikan masalah yang ada. 💤"
      },
      {
        "question": "Bagaimana pola tidur Anda saat sedang stres atau tertekan?",
        "options": [
          "Sangat baik",
          "Cukup baik",
          "Biasa saja",
          "Kurang baik",
          "Sangat buruk"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Tidur yang baik saat stres adalah tanda kesehatan mental yang baik! 😊",
        "badTips": "Tidur yang buruk saat stres bisa memperburuk kondisi Anda. Cobalah untuk memperbaiki pola tidur Anda. 😴",
        "neutralTips": "Tidur yang biasa saja mungkin tidak ideal, coba cari cara untuk meningkatkan kualitas tidur. ⏳"
      },
      {
        "question": "Apakah Anda pernah merasa kehilangan motivasi dalam menjalani aktivitas sehari-hari?",
        "options": [
          "Tidak pernah",
          "Jarang",
          "Kadang-kadang",
          "Sering",
          "Setiap hari"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Tidak merasa kehilangan motivasi adalah tanda mental yang sehat! 😊",
        "badTips": "Jika Anda merasa kehilangan motivasi setiap hari, pertimbangkan untuk berbicara dengan profesional. 😟",
        "neutralTips": "Kadang merasa kehilangan motivasi itu normal, tapi coba cari cara untuk memotivasi diri Anda. 🌈"
      },
      {
        "question": "Seberapa sering Anda merasa kesepian?",
        "options": [
          "Tidak pernah",
          "Jarang",
          "Kadang-kadang",
          "Sering",
          "Setiap hari"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Tidak merasa kesepian adalah tanda hubungan sosial yang baik! 😊",
        "badTips": "Jika Anda merasa kesepian setiap hari, pertimbangkan untuk berbicara dengan seseorang. ❤️",
        "neutralTips": "Kadang merasa kesepian itu normal, tapi coba cari cara untuk terhubung dengan orang lain. 🌿"
      },
      {
        "question": "Bagaimana cara Anda menjaga kesehatan mental sehari-hari?",
        "options": [
          "Olahraga",
          "Meditasi",
          "Berkumpul dengan teman",
          "Menulis jurnal",
          "Tidak melakukan apa-apa"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Olahraga adalah cara yang sangat baik untuk menjaga kesehatan mental! 😊",
        "badTips": "Tidak melakukan apa-apa bisa memperburuk kesehatan mental. Cobalah untuk mencari aktivitas yang positif. 🌈",
        "neutralTips": "Kadang menulis jurnal bisa membantu, tapi lebih baik jika Anda melakukan beberapa aktivitas positif. 📖"
      },
      {
        "question": "Apa yang biasanya memicu stres terbesar dalam hidup Anda?",
        "options": [
          "Pekerjaan",
          "Keluarga",
          "Keuangan",
          "Kesehatan",
          "Tidak ada"
        ],
        "scores": [-2, -1, 3, 5, 6],
        "goodTips": "Tidak memiliki pemicu stres adalah tanda kesehatan mental yang baik! 😊",
        "badTips": "Jika pekerjaan atau keuangan menjadi pemicu stres, coba cari cara untuk mengelolanya. 💼",
        "neutralTips": "Kadang stres itu normal, tapi coba cari cara untuk mengurangi pemicu stres. 🌿"
      },
      {
        "question": "Jika mengalami hari buruk, apa yang bisa membuat Anda merasa lebih baik?",
        "options": [
          "Berbicara dengan teman",
          "Olahraga",
          "Menonton film",
          "Tidur",
          "Tidak ada yang bisa membantu"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Berbicara dengan teman adalah cara yang baik untuk merasa lebih baik! 😊",
        "badTips": "Tidak ada yang bisa membantu bisa membuat Anda merasa terjebak. Cobalah untuk mencari dukungan. ❤️",
        "neutralTips": "Kadang menonton film bisa membantu, tapi lebih baik jika Anda berbicara dengan seseorang. 🎬"
      },
      {
        "question": "Apakah Anda pernah melakukan meditasi atau latihan pernapasan untuk relaksasi?",
        "options": [
          "Selalu",
          "Sering",
          "Kadang-kadang",
          "Jarang",
          "Tidak pernah"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Meditasi adalah cara yang sangat baik untuk relaksasi! 😊",
        "badTips": "Tidak pernah mencoba meditasi bisa membuat Anda kehilangan manfaatnya. Cobalah untuk mencobanya. 🧘‍♂️",
        "neutralTips": "Kadang melakukan meditasi itu baik, tapi lebih baik jika dilakukan secara rutin. ⏳"
      },
      {
        "question": "Bagaimana cara Anda menghadapi kritik atau kegagalan?",
        "options": [
          "Menerima dan belajar",
          "Merasa sedih",
          "Mengabaikan",
          "Marah",
          "Tidak tahu"
        ],
        "scores": [6, -1, -1, -1, -2],
        "goodTips": "Menerima kritik dengan baik adalah tanda mental yang sehat! 😊",
        "badTips": "Merasa marah bisa menghambat pertumbuhan pribadi. Cobalah untuk melihat kritik sebagai kesempatan untuk belajar. 😟",
        "neutralTips": "Kadang merasa sedih itu normal, tapi coba cari cara untuk bangkit kembali. 🌈"
      },
      {
        "question": "Jika merasa lelah secara emosional, apa yang Anda lakukan?",
        "options": [
          "Istirahat dan relaksasi",
          "Berbicara dengan teman",
          "Menonton film",
          "Tidur",
          "Tidak melakukan apa-apa"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Istirahat dan relaksasi adalah cara yang baik untuk mengatasi kelelahan emosional! 😊",
        "badTips": "Tidak melakukan apa-apa bisa memperburuk keadaan. Cobalah untuk mencari cara untuk merasa lebih baik. ❤️",
        "neutralTips": "Kadang menonton film bisa membantu, tapi lebih baik jika Anda juga berbicara dengan seseorang. 🎬"
      },
      {
        "question": "Seberapa penting menurut Anda waktu untuk diri sendiri (me time)?",
        "options": [
          "Sangat penting",
          "Cukup penting",
          "Biasa saja",
          "Kurang penting",
          "Tidak penting"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Waktu untuk diri sendiri sangat penting untuk kesehatan mental! 😊",
        "badTips": "Tidak memberikan waktu untuk diri sendiri bisa membuat Anda merasa tertekan. Cobalah untuk menyisihkan waktu untuk diri sendiri. 🧘‍♂️",
        "neutralTips": "Kadang waktu untuk diri sendiri itu baik, tapi lebih baik jika dilakukan secara rutin. ⏳"
      },
      {
        "question": "Jika memiliki banyak pikiran negatif, bagaimana cara Anda mengatasinya?",
        "options": [
          "Berbicara dengan seseorang",
          "Menulis jurnal",
          "Meditasi",
          "Mengabaikannya",
          "Tidak tahu"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Berbicara dengan seseorang adalah cara yang baik untuk mengatasi pikiran negatif! 😊",
        "badTips": "Mengabaikan pikiran negatif tidak akan menyelesaikan masalah. Cobalah untuk mencari cara untuk menghadapinya. 😟",
        "neutralTips": "Kadang menulis jurnal bisa membantu, tapi lebih baik jika Anda juga melakukan beberapa aktivitas positif. 📖"
      }
    ]
  },
  "nutrition": {
    "title": "Sehatkah pilihan makananmu? 🥗",
    "desc": "Makan sehat atau asal kenyang? \nYuk, cek pola makanmu dan dapatkan tips hidup lebih sehat!",
    "questions": [
      {
        "question": "Seberapa sering Anda sarapan setiap pagi?",
        "options": [
          "Setiap hari",
          "Sering",
          "Kadang-kadang",
          "Jarang",
          "Hampir tidak pernah"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Sarapan setiap hari sangat baik untuk energi dan metabolisme. Pertahankan kebiasaan ini! 🥗",
        "badTips": "Tidak sarapan bisa membuat tubuh lemas dan kurang fokus. Cobalah untuk makan sesuatu di pagi hari. 🍞",
        "neutralTips": "Kadang sarapan lebih baik daripada tidak sama sekali, tapi coba buat jadwal lebih rutin. ⏰"
      },
      {
        "question": "Apa jenis makanan yang paling sering Anda konsumsi sehari-hari?",
        "options": [
          "Makanan sehat",
          "Makanan cepat saji",
          "Makanan olahan",
          "Makanan manis",
          "Makanan berlemak"
        ],
        "scores": [6, -1, -1, -1, -2],
        "goodTips": "Mengonsumsi makanan sehat sangat baik untuk kesehatan! 😊",
        "badTips": "Terlalu sering mengonsumsi makanan cepat saji bisa meningkatkan risiko penyakit. Coba kurangi ya! 🍔➡️🚫",
        "neutralTips": "Kadang mengonsumsi makanan olahan itu wajar, tapi lebih baik jika lebih banyak makanan sehat. 🥦"
      },
      {
        "question": "Berapa banyak air yang Anda minum dalam sehari?",
        "options": [
          "Lebih dari 2 liter",
          "1-2 liter",
          "0.5-1 liter",
          "Kurang dari 0.5 liter",
          "Tidak pernah minum air"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Minum lebih dari 2 liter air sangat baik untuk kesehatan! 😊",
        "badTips": "Tidak minum air bisa menyebabkan dehidrasi. Cobalah untuk selalu membawa botol air. 💧",
        "neutralTips": "Kadang minum air itu baik, tapi lebih baik jika dilakukan secara rutin. 🚰"
      },
      {
        "question": "Seberapa sering Anda makan makanan cepat saji atau junk food?",
        "options": [
          "Setiap hari",
          "Sering",
          "Kadang-kadang",
          "Jarang",
          "Hampir tidak pernah"
        ],
        "scores": [-2, -1, 3, 5, 6],
        "goodTips": "Bagus! Mengurangi makanan cepat saji membantu menjaga kesehatan tubuh. 🥗",
        "badTips": "Terlalu sering makan fast food bisa meningkatkan risiko penyakit. Coba kurangi ya! 🍔➡️🚫",
        "neutralTips": "Sesekali boleh, tapi pastikan tetap menyeimbangkan dengan makanan sehat. 🥦"
      },
      {
        "question": "Apakah Anda lebih sering makan karena lapar atau sekadar kebiasaan?",
        "options": [
          "Selalu lapar",
          "Sering lapar",
          "Kadang-kadang lapar",
          "Jarang lapar",
          "Tidak pernah lapar"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Makan karena lapar adalah tanda pola makan yang sehat! 😊",
        "badTips": "Makan tanpa lapar bisa menyebabkan kelebihan kalori. Cobalah untuk lebih sadar saat makan. 🍽️",
        "neutralTips": "Kadang makan karena kebiasaan itu normal, tapi coba perhatikan saat Anda makan. ⏳"
      },
      {
        "question": "Seberapa sering Anda mengonsumsi sayur dan buah dalam sehari?",
        "options": [
          "Setiap hari",
          "Sering",
          "Kadang-kadang",
          "Jarang",
          "Hampir tidak pernah"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Mengonsumsi sayur dan buah setiap hari sangat baik untuk kesehatan! 😊",
        "badTips": "Jarang mengonsumsi sayur dan buah bisa menyebabkan kekurangan nutrisi. Cobalah untuk menambah porsi sayur dan buah dalam diet Anda. 🥗",
        "neutralTips": "Kadang mengonsumsi sayur dan buah itu baik, tapi lebih baik jika dilakukan secara rutin. 🥦"
      },
      {
        "question": "Jika merasa stres, bagaimana pola makan Anda berubah?",
        "options": [
          "Makan lebih sehat",
          "Makan lebih banyak",
          "Makan lebih sedikit",
          "Tidak ada perubahan",
          "Makan junk food"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Makan lebih sehat saat stres adalah tanda manajemen stres yang baik! 😊",
        "badTips": "Makan junk food saat stres bisa memperburuk kesehatan. Cobalah untuk memilih makanan yang lebih sehat. 🍔➡️🚫",
        "neutralTips": "Kadang pola makan berubah itu normal, tapi coba cari cara untuk tetap sehat. 🌿"
      },
      {
        "question": "Apakah Anda memiliki jadwal makan yang teratur?",
        "options": [
          "Selalu teratur",
          "Cukup teratur",
          "Biasa saja",
          "Kurang teratur",
          "Sangat tidak teratur"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Jadwal makan yang teratur sangat baik untuk kesehatan! 😊",
        "badTips": "Jadwal yang tidak teratur bisa mengganggu metabolisme. Cobalah untuk membuat jadwal yang lebih baik. ⏰",
        "neutralTips": "Jadwal yang biasa saja mungkin tidak ideal, coba cari cara untuk membuatnya lebih baik. 🍽️"
      },
      {
        "question": "Seberapa sering Anda ngemil di malam hari?",
        "options": [
          "Setiap malam",
          "Sering",
          "Kadang-kadang",
          "Jarang",
          "Hampir tidak pernah"
        ],
        "scores": [-2, -1, 3, 5, 6],
        "goodTips": "Jarang ngemil di malam hari sangat baik untuk kesehatan! 😊",
        "badTips": "Sering ngemil di malam hari bisa menyebabkan penambahan berat badan. Cobalah untuk menghindarinya. 🍪➡️🚫",
        "neutralTips": "Kadang ngemil itu normal, tapi coba pilih camilan yang lebih sehat. 🥕"
      },
      {
        "question": "Bagaimana cara Anda memilih makanan saat berbelanja?",
        "options": [
          "Selalu memilih makanan sehat",
          "Cukup memilih makanan sehat",
          "Biasa saja",
          "Jarang memilih makanan sehat",
          "Tidak pernah memilih makanan sehat"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Memilih makanan sehat saat berbelanja sangat baik untuk kesehatan! 😊",
        "badTips": "Tidak pernah memilih makanan sehat bisa berdampak buruk bagi kesehatan. Cobalah untuk lebih sadar saat berbelanja. 🛒",
        "neutralTips": "Kadang memilih makanan sehat itu baik, tapi lebih baik jika dilakukan secara rutin. 🥦"
      },
      {
        "question": "Apakah Anda pernah mencoba mengatur pola makan yang lebih sehat?",
        "options": [
          "Selalu",
          "Sering",
          "Kadang-kadang",
          "Jarang",
          "Tidak pernah"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Mengatur pola makan yang sehat adalah langkah yang sangat baik! 😊",
        "badTips": "Tidak pernah mencoba mengatur pola makan bisa membuat Anda kehilangan manfaat kesehatan. Cobalah untuk mencobanya. 🥗",
        "neutralTips": "Kadang mencoba pola makan yang lebih sehat itu baik, tapi lebih baik jika dilakukan secara rutin. ⏳"
      },
      {
        "question": "Seberapa sering Anda mengonsumsi makanan tinggi gula atau minuman manis?",
        "options": [
          "Setiap hari",
          "Sering",
          "Kadang-kadang",
          "Jarang",
          "Hampir tidak pernah"
        ],
        "scores": [-2, -1, 3, 5, 6],
        "goodTips": "Mengurangi makanan tinggi gula sangat baik untuk kesehatan! 😊",
        "badTips": "Terlalu sering mengonsumsi makanan tinggi gula bisa meningkatkan risiko penyakit. Coba kurangi ya! 🍭➡️🚫",
        "neutralTips": "Sesekali boleh, tapi pastikan tetap menyeimbangkan dengan makanan sehat. 🍏"
      },
      {
        "question": "Bagaimana kebiasaan makan Anda saat bekerja atau belajar?",
        "options": [
          "Selalu teratur",
          "Cukup teratur",
          "Biasa saja",
          "Kurang teratur",
          "Sangat tidak teratur"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Kebiasaan makan yang teratur saat bekerja sangat baik untuk kesehatan! 😊",
        "badTips": "Kebiasaan yang tidak teratur bisa mengganggu metabolisme. Cobalah untuk membuat kebiasaan yang lebih baik. ⏰",
        "neutralTips": "Kebiasaan yang biasa saja mungkin tidak ideal, coba cari cara untuk membuatnya lebih baik. 🍽️"
      },
      {
        "question": "Seberapa sering Anda mencoba makanan baru yang sehat?",
        "options": [
          "Setiap minggu",
          "Sering",
          "Kadang-kadang",
          "Jarang",
          "Hampir tidak pernah"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Mencoba makanan baru yang sehat sangat baik untuk variasi diet! 😊",
        "badTips": "Jarang mencoba makanan baru bisa membuat diet Anda monoton. Cobalah untuk lebih berani mencoba. 🍽️",
        "neutralTips": "Kadang mencoba makanan baru itu baik, tapi lebih baik jika dilakukan secara rutin. 🥗"
      },
      {
        "question": "Apakah Anda pernah mengalami gangguan pencernaan akibat pola makan?",
        "options": [
          "Tidak pernah",
          "Jarang",
          "Kadang-kadang",
          "Sering",
          "Setiap hari"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Tidak mengalami gangguan pencernaan adalah tanda pola makan yang baik! 😊",
        "badTips": "Sering mengalami gangguan pencernaan bisa jadi tanda pola makan yang buruk. Cobalah untuk memperbaikinya. 😟",
        "neutralTips": "Kadang mengalami gangguan pencernaan itu normal, tapi coba cari tahu penyebabnya. 🌿"
      },
      {
        "question": "Jika lapar di malam hari, apa yang biasanya Anda makan?",
        "options": [
          "Makanan sehat",
          "Camilan ringan",
          "Makanan cepat saji",
          "Makanan manis",
          "Tidak makan"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Memilih makanan sehat saat lapar di malam hari sangat baik untuk kesehatan! 😊",
        "badTips": "Makan makanan cepat saji atau manis bisa berdampak buruk bagi kesehatan. Cobalah untuk memilih camilan yang lebih sehat. 🍏",
        "neutralTips": "Kadang ngemil itu normal, tapi coba pilih camilan yang lebih sehat. 🥕"
      },
      {
        "question": "Seberapa sering Anda makan sambil menonton TV atau bermain HP?",
        "options": [
          "Tidak pernah",
          "Jarang",
          "Kadang-kadang",
          "Sering",
          "Setiap hari"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Tidak makan sambil menonton TV adalah tanda kesadaran saat makan! 😊",
        "badTips": "Makan sambil menonton bisa membuat Anda tidak sadar dengan porsi yang dimakan. Cobalah untuk fokus saat makan. 📺",
        "neutralTips": "Kadang makan sambil menonton itu normal, tapi lebih baik jika Anda fokus pada makanan. 🍽️"
      },
      {
        "question": "Bagaimana cara Anda memastikan asupan gizi harian Anda cukup?",
        "options": [
          "Menghitung kalori",
          "Menggunakan aplikasi",
          "Membaca label makanan",
          "Tidak melakukan apa-apa",
          "Tidak tahu"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Menghitung kalori adalah cara yang baik untuk memastikan asupan gizi! 😊",
        "badTips": "Tidak melakukan apa-apa bisa membuat Anda kehilangan kontrol atas pola makan. Cobalah untuk lebih sadar saat makan. 🍽️",
        "neutralTips": "Kadang menggunakan aplikasi itu baik, tapi lebih baik jika Anda juga memperhatikan makanan yang dimakan. 📱"
      },
      {
        "question": "Seberapa penting keseimbangan antara karbohidrat, protein, dan lemak bagi Anda?",
        "options": [
          "Sangat penting",
          "Cukup penting",
          "Biasa saja",
          "Kurang penting",
          "Tidak penting"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Keseimbangan nutrisi sangat penting untuk kesehatan! 😊",
        "badTips": "Tidak memperhatikan keseimbangan bisa berdampak buruk bagi kesehatan. Cobalah untuk lebih sadar saat makan. 🍽️",
        "neutralTips": "Kadang memperhatikan keseimbangan itu baik, tapi lebih baik jika dilakukan secara rutin. 🥗"
      },
      {
        "question": "Jika harus memilih antara makanan sehat dan makanan enak tapi kurang sehat, mana yang Anda pilih?",
        "options": [
          "Selalu makanan sehat",
          "Cukup memilih makanan sehat",
          "Biasa saja",
          "Jarang memilih makanan sehat",
          "Selalu makanan enak"
        ],
        "scores": [6, 5, 3, -1, -2],
        "goodTips": "Memilih makanan sehat adalah langkah yang sangat baik untuk kesehatan! 😊",
        "badTips": "Selalu memilih makanan enak bisa berdampak buruk bagi kesehatan. Cobalah untuk lebih sadar saat memilih makanan. 🍔➡️🚫",
        "neutralTips": "Kadang memilih makanan enak itu normal, tapi lebih baik jika Anda juga memperhatikan kesehatan. 🥗"
      }
    ]
  }
}


function setupWelcomePage(category) {
    console.log("Cek kategori:", category);
    console.log("Data kategori:", categoryData);

    if (!categoryData[category]) {
        console.error("Kategori tidak ditemukan dalam categoryData!");
        return;
    }

    const categoryInfo = categoryData[category];

    console.log("Kategori Info:", categoryInfo);
    console.log("Kategori yang dicari:", category);
    console.log("Kategori yang tersedia:", Object.keys(categoryData));


    document.getElementById("quizTitle").innerHTML = `${categoryInfo.title}`;
    document.getElementById("quizDesc").innerText = categoryInfo.desc;
    document.getElementById("welcome").style.borderColor = categoryInfo.color;
}


window.onload = function () {
    const urlParams = new URLSearchParams(window.location.search);
    
    console.log("Kategori dari URL:", category);

    setupWelcomePage(category); 
};


let currentQuestion = 0;
let score = 0;
let userAnswers = [];

function startQuiz() {
    document.getElementById("welcome").style.display = "none";
    document.getElementById("quiz").style.display = "block";
    loadQuestion();
    
}

const urlParams = new URLSearchParams(window.location.search);
const category = urlParams.get("category") || "sleep";

console.log("Kategori yang dipilih:", category);


function loadQuestion() {
    if (!categoryData[category]) {
        console.error("Kategori tidak ditemukan:", category);
        document.getElementById("questionText").innerText = "Kategori tidak ditemukan!";
        return;
    }

    const data = categoryData[category];

    if (currentQuestion >= data.questions.length) {
        showResult();
        return;
    }

    const questionData = data.questions[currentQuestion];
    document.getElementById("questionText").innerText = questionData.question;
    document.getElementById("questionNumber").innerText = currentQuestion + 1;

    const optionsDiv = document.getElementById("options");
    optionsDiv.innerHTML = "";

    questionData.options.forEach((option, index) => {
        const button = document.createElement("button");
        button.classList.add("button");
        button.innerText = option;
        button.onclick = () => selectAnswer(index);
        optionsDiv.appendChild(button);
    });
}

function selectAnswer(index) {
  const questionData = categoryData[category].questions[currentQuestion];
  const selectedAnswer = questionData.options[index];

  const isPositive = questionData.context === "positive";
  const scoreMapping = isPositive ? [-2, -1, 2, 4, 6] : [5, 4, 2, -1, -2]; 

  let points = scoreMapping[index];
  let bgColor = "#FFEE58";
  let tipsMessage = questionData.neutralTips || "Tetap semangat menjaga kesehatan! 💪";

  if (points >= 4) {
      bgColor = "#99FF99";
      tipsMessage = questionData.goodTips;
  } else if (points <= -1) {
      bgColor = "#FF6666";
      tipsMessage = questionData.badTips;
  }

  score += points; // Tambahkan skor

  userAnswers.push({
      question: questionData.question,
      selected: selectedAnswer,
      tips: tipsMessage,
      color: bgColor
  });

  currentQuestion++;
  if (currentQuestion < categoryData[category].questions.length) {
      loadQuestion();
  } else {
      showResult();
  }
}




function getFinalTips(score) {
if (score >= 75) return "Luar biasa! Kebiasaan sehatmu sangat baik, pertahankan! 🌟";
if (score >= 50) return "Bagus! Kamu memiliki kebiasaan sehat yang cukup baik, tetap tingkatkan ya! 💪";
if (score >= 25) return "Cukup baik, tetapi masih ada ruang untuk perbaikan. Kamu bisa lebih baik lagi! 😊";
return "Kamu perlu lebih perhatian terhadap kebiasaan sehatmu. Yuk, mulai perubahan kecil dari sekarang! 🔥";
}

function showResult() {
const data = categoryData[category];

document.getElementById("quiz").innerHTML = `<h2>Quiz Selesai!</h2>
<p>Skor Anda: ${score}</p>
<p>${getFinalTips(score)}</p>`;
document.getElementById("reviewButton").style.display = "block";
document.getElementById("restartButton").style.display = "block";
generateReviews();
}

function generateReviews() {
    let reviewHTML = '<h2 style="text-align:center;text-transform:uppercase;">Review Jawaban</h2>';
    userAnswers.forEach((answer, index) => {
        reviewHTML += `<div class="review-box">
        <p><strong>Pertanyaan ${index + 1}:</strong> ${answer.question}</p>
        <p>Jawaban Anda: <span style="background-color: ${answer.color}; padding: 5px; border-radius: 3px;">${answer.selected}</span></p>
        <p><strong>Tips:</strong> ${answer.tips}</p>
        <hr>
        </div>`;
    });
    document.getElementById("review-scroll").innerHTML = reviewHTML;
}

function toggleReview() {
    const reviewDiv = document.getElementById("review-scroll");
    const reviewButton = document.getElementById("reviewButton");
    if (reviewDiv.style.display === "none" || reviewDiv.style.display === "") {
        reviewDiv.style.display = "block";
        reviewButton.innerText = "Hide Review";
    } else {
        reviewDiv.style.display = "none";
        reviewButton.innerText = "Show Review";
    }
}

function restartQuiz() {
    location.reload();
}