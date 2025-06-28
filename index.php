<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Petualangan Angka Ajaib</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Mochiy+Pop+One&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Mochiy Pop One', sans-serif;
            touch-action: none; /* Mencegah zoom/scroll di perangkat mobile saat drag */
        }
        .fruit {
            width: 50px; /* Sedikit lebih kecil untuk mobile */
            height: 50px;
            cursor: move;
            user-select: none; /* Mencegah teks terseleksi saat drag */
            transition: transform 0.2s ease;
        }
        .dragging {
            opacity: 0.5;
            transform: scale(1.2);
            z-index: 1000;
        }
        .drop-zone {
            transition: background-color 0.3s, border-color 0.3s;
        }
        .drag-over {
            background-color: #c8e6c9 !important; /* Hijau muda saat item di atasnya */
            border-color: #4caf50 !important;
        }
        .button-active {
            transform: scale(1.1);
            box-shadow: 0 0 20px rgba(251, 191, 36, 0.8);
            background-color: #f59e0b !important;
        }
        /* Style untuk kanvas tulis */
        #drawing-canvas {
            border: 3px solid #cbd5e1;
            border-radius: 0.75rem;
            cursor: crosshair;
            touch-action: none; /* Penting untuk menggambar di mobile */
        }
    </style>
</head>
<body class="bg-blue-100 flex items-center justify-center min-h-screen p-2 sm:p-4">

    <div class="w-full max-w-2xl bg-white rounded-2xl shadow-2xl p-4 md:p-6 text-center">
        
        <h1 class="text-2xl md:text-4xl text-blue-600 mb-4">Petualangan Angka Ajaib</h1>
        
        <!-- Tombol Pilihan Mode -->
        <div class="flex justify-center gap-2 mb-4">
            <button id="btn-penjumlahan" class="px-4 py-2 text-base md:px-6 md:py-3 bg-yellow-400 hover:bg-yellow-500 text-white font-bold rounded-lg shadow-md md:text-xl transition-transform transform duration-200">Penjumlahan (+)</button>
            <button id="btn-pengurangan" class="px-4 py-2 text-base md:px-6 md:py-3 bg-pink-400 hover:bg-pink-500 text-white font-bold rounded-lg shadow-md md:text-xl transition-transform transform duration-200">Pengurangan (-)</button>
        </div>

        <!-- Area Soal -->
        <div id="problem-area" class="bg-gray-100 rounded-lg p-3 mb-4 text-3xl md:text-5xl text-gray-700 flex justify-center items-center gap-2 md:gap-4">
            <span id="num1"></span>
            <span id="operator"></span>
            <span id="num2"></span>
            <span>=</span>
            <span id="answer-box" class="bg-white w-20 h-20 md:w-24 md:h-24 rounded-lg flex items-center justify-center shadow-inner text-gray-400">?</span>
        </div>
        
        <!-- Panggung Permainan -->
        <div id="workspace" class="flex flex-col gap-4 min-h-[250px]">
            <!-- Area Sumber Benda (Untuk Penjumlahan) -->
            <div id="source-area" class="grid grid-cols-2 gap-2">
                <div id="source1" class="bg-blue-50 border-2 border-dashed border-blue-300 rounded-lg p-2 min-h-[120px] flex flex-wrap gap-1 justify-center items-center"></div>
                <div id="source2" class="bg-green-50 border-2 border-dashed border-green-300 rounded-lg p-2 min-h-[120px] flex flex-wrap gap-1 justify-center items-center"></div>
            </div>

            <!-- Area Target (Keranjang Jawaban) -->
            <div id="target-area" class="flex justify-center items-center flex-col">
                 <h2 id="instruction" class="text-lg md:text-xl text-gray-600 mb-2"></h2>
                 <div id="basket" class="drop-zone w-full bg-orange-100 border-4 border-dashed border-orange-400 rounded-2xl p-2 min-h-[150px] flex flex-wrap gap-1 justify-center items-start">
                 </div>
            </div>
        </div>
        
        <!-- Area Tulis Jawaban -->
        <div id="drawing-area" class="mt-6">
            <h3 class="text-lg md:text-xl font-semibold text-gray-700 mb-2">Sekarang, coba tulis jawabannya di sini!</h3>
            <canvas id="drawing-canvas" width="300" height="150"></canvas>
            <div class="flex justify-center gap-4 mt-3">
                 <button id="btn-check" class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white font-bold rounded-lg shadow-lg text-lg md:text-xl transition-transform transform duration-200">Cek Jawaban</button>
                 <button id="btn-clear-canvas" class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white font-bold rounded-lg shadow-lg text-lg md:text-xl transition-transform transform duration-200">Hapus Tulisan</button>
            </div>
        </div>

        <!-- Tombol Soal Baru & Pesan -->
        <div class="mt-4 flex flex-col items-center justify-center gap-4">
            <div id="feedback" class="text-2xl md:text-3xl font-bold h-10"></div>
            <button id="btn-new" class="w-full md:w-auto px-8 py-4 bg-purple-500 hover:bg-purple-600 text-white font-bold rounded-lg shadow-lg text-xl md:text-2xl transition-transform transform duration-200">Soal Baru</button>
        </div>

    </div>

    <script>
        // --- Inisialisasi Elemen DOM ---
        const num1El = document.getElementById('num1');
        const num2El = document.getElementById('num2');
        const operatorEl = document.getElementById('operator');
        const answerBoxEl = document.getElementById('answer-box');
        const source1El = document.getElementById('source1');
        const source2El = document.getElementById('source2');
        const basketEl = document.getElementById('basket');
        const feedbackEl = document.getElementById('feedback');
        const instructionEl = document.getElementById('instruction');
        const btnPenjumlahan = document.getElementById('btn-penjumlahan');
        const btnPengurangan = document.getElementById('btn-pengurangan');
        const btnCheck = document.getElementById('btn-check');
        const btnNew = document.getElementById('btn-new');
        const btnClearCanvas = document.getElementById('btn-clear-canvas');
        
        // Canvas Elements
        const canvas = document.getElementById('drawing-canvas');
        const ctx = canvas.getContext('2d');

        // --- Variabel Status Permainan ---
        let currentMode = 'penjumlahan';
        let number1, number2, correctAnswer;
        const fruits = ['🍎', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🍑'];

        // --- Logika Menggambar di Kanvas ---
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        function getMousePos(canvasDom, mouseEvent) {
            const rect = canvasDom.getBoundingClientRect();
            return {
                x: mouseEvent.clientX - rect.left,
                y: mouseEvent.clientY - rect.top
            };
        }

        function getTouchPos(canvasDom, touchEvent) {
            const rect = canvasDom.getBoundingClientRect();
            return {
                x: touchEvent.touches[0].clientX - rect.left,
                y: touchEvent.touches[0].clientY - rect.top
            };
        }
        
        function startDrawing(e) {
            e.preventDefault();
            isDrawing = true;
            let pos;
            if (e.touches) pos = getTouchPos(canvas, e);
            else pos = getMousePos(canvas, e);
            [lastX, lastY] = [pos.x, pos.y];
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            let pos;
            if (e.touches) pos = getTouchPos(canvas, e);
            else pos = getMousePos(canvas, e);
            
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            [lastX, lastY] = [pos.x, pos.y];
        }

        function stopDrawing() {
            isDrawing = false;
        }

        function clearCanvas() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        function setupCanvas() {
            // Setup konteks gambar
            ctx.strokeStyle = '#333';
            ctx.lineWidth = 8;
            ctx.lineJoin = 'round';
            ctx.lineCap = 'round';

            // Mouse events
            canvas.addEventListener('mousedown', startDrawing);
            canvas.addEventListener('mousemove', draw);
            canvas.addEventListener('mouseup', stopDrawing);
            canvas.addEventListener('mouseout', stopDrawing);

            // Touch events
            canvas.addEventListener('touchstart', startDrawing);
            canvas.addEventListener('touchmove', draw);
            canvas.addEventListener('touchend', stopDrawing);
            
            btnClearCanvas.addEventListener('click', clearCanvas);
        }

        // --- Fungsi Utama Permainan ---

        function generateProblem() {
            // Reset semuanya
            feedbackEl.textContent = '';
            answerBoxEl.textContent = '?';
            answerBoxEl.classList.remove('text-green-500', 'text-red-500');
            basketEl.innerHTML = '';
            source1El.innerHTML = '';
            source2El.innerHTML = '';
            source1El.style.display = 'grid'; // pakai grid agar konsisten
            source2El.style.display = 'grid';
            clearCanvas();

            number1 = Math.floor(Math.random() * 5) + 1;
            number2 = Math.floor(Math.random() * 4) + 1; // Maks 4 agar total tidak > 9
            const randomFruit = fruits[Math.floor(Math.random() * fruits.length)];

            if (currentMode === 'penjumlahan') {
                correctAnswer = number1 + number2;
                operatorEl.textContent = '+';
                instructionEl.textContent = 'Ayo gabungkan semua buah ke dalam keranjang!';
                for (let i = 0; i < number1; i++) source1El.appendChild(createFruit(randomFruit, `fruit-s1-${i}`));
                for (let i = 0; i < number2; i++) source2El.appendChild(createFruit(randomFruit, `fruit-s2-${i}`));
            } else { // Mode Pengurangan
                if (number1 < number2) [number1, number2] = [number2, number1];
                correctAnswer = number1 - number2;
                operatorEl.textContent = '-';
                instructionEl.textContent = `Ayo ambil ${number2} buah dari keranjang!`;
                for (let i = 0; i < number1; i++) basketEl.appendChild(createFruit(randomFruit, `fruit-b-${i}`));
                source1El.style.display = 'none';
                source2El.style.display = 'none';
            }
            num1El.textContent = number1;
            num2El.textContent = number2;
        }

        function createFruit(emoji, id) {
            const fruitEl = document.createElement('div');
            fruitEl.textContent = emoji;
            fruitEl.id = id;
            fruitEl.className = 'fruit text-4xl md:text-5xl';
            fruitEl.draggable = true;
            fruitEl.addEventListener('dragstart', handleDragStart);
            fruitEl.addEventListener('dragend', handleDragEnd);
            return fruitEl;
        }

        function checkAnswer() {
            const userAnswer = basketEl.children.length;
            
            if (userAnswer === correctAnswer) {
                feedbackEl.textContent = 'Benar! Hebat! 🎉';
                feedbackEl.className = 'text-2xl md:text-3xl font-bold text-green-500 h-10';
                answerBoxEl.textContent = correctAnswer;
                answerBoxEl.classList.add('text-green-500');
                answerBoxEl.classList.remove('text-red-500', 'text-gray-400');
            } else {
                feedbackEl.textContent = 'Coba Lagi Yuk! 🤔';
                feedbackEl.className = 'text-2xl md:text-3xl font-bold text-red-500 h-10';
                answerBoxEl.textContent = '?'; 
                answerBoxEl.classList.add('text-red-500');
                answerBoxEl.classList.remove('text-green-500', 'text-gray-400');
            }
        }

        // --- Event Handlers untuk Drag and Drop ---

        function handleDragStart(e) {
            e.dataTransfer.setData('text/plain', e.target.id);
            setTimeout(() => e.target.classList.add('dragging'), 0);
        }

        function handleDragEnd(e) {
            e.target.classList.remove('dragging');
        }

        function setupDropZones() {
            const dropZones = [basketEl, source1El, source2El];
            dropZones.forEach(zone => {
                zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag-over'); });
                zone.addEventListener('dragleave', e => { zone.classList.remove('drag-over'); });
                zone.addEventListener('drop', e => {
                    e.preventDefault();
                    zone.classList.remove('drag-over');
                    const id = e.dataTransfer.getData('text/plain');
                    const draggable = document.getElementById(id);
                    if (draggable) zone.appendChild(draggable);
                });
            });
        }
        
        // --- Inisialisasi Event Listener Tombol ---
        btnPenjumlahan.addEventListener('click', () => {
            currentMode = 'penjumlahan';
            btnPenjumlahan.classList.add('button-active');
            btnPengurangan.classList.remove('button-active');
            generateProblem();
        });

        btnPengurangan.addEventListener('click', () => {
            currentMode = 'pengurangan';
            btnPengurangan.classList.add('button-active');
            btnPenjumlahan.classList.remove('button-active');
            generateProblem();
        });

        btnCheck.addEventListener('click', checkAnswer);
        btnNew.addEventListener('click', generateProblem);

        // --- Mulai Permainan ---
        window.addEventListener('DOMContentLoaded', () => {
            btnPenjumlahan.classList.add('button-active');
            setupDropZones();
            setupCanvas();
            generateProblem();
        });

    </script>
</body>
</html>
