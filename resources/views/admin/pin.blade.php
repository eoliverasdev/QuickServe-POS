<style>
    .pin-wrapper { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100vh; font-family: sans-serif; background: #2c3e50; color: white; }
    .pin-display { font-size: 3rem; margin-bottom: 20px; letter-spacing: 10px; background: #fff; color: #333; padding: 10px 40px; border-radius: 10px; min-width: 150px; text-align: center; }
    .keypad { display: grid; grid-template-columns: repeat(3, 80px); gap: 15px; }
    .key { width: 80px; height: 80px; border-radius: 50%; border: 2px solid white; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; cursor: pointer; transition: 0.2s; }
    .key:active { background: white; color: #2c3e50; }
    .key.clear { border-color: #e74c3c; color: #e74c3c; }
</style>

<div class="pin-wrapper">
    <h2>Accés Encarregada</h2>
    <div id="display" class="pin-display">****</div>

    <form id="pin-form" action="{{ route('admin.check-pin') }}" method="POST">
        @csrf
        <input type="hidden" name="pin" id="pin-input">
        <div class="keypad">
            <div class="key" onclick="press(1)">1</div>
            <div class="key" onclick="press(2)">2</div>
            <div class="key" onclick="press(3)">3</div>
            <div class="key" onclick="press(4)">4</div>
            <div class="key" onclick="press(5)">5</div>
            <div class="key" onclick="press(6)">6</div>
            <div class="key" onclick="press(7)">7</div>
            <div class="key" onclick="press(8)">8</div>
            <div class="key" onclick="press(9)">9</div>
            <div class="key clear" onclick="clearPin()">C</div>
            <div class="key" onclick="press(0)">0</div>
            <div class="key" style="border-color: #2ecc71; color: #2ecc71;" onclick="submitPin()">OK</div>
        </div>
    </form>
</div>

<script>
    let currentPin = "";
    function press(n) {
        if(currentPin.length < 4) {
            currentPin += n;
            updateDisplay();
        }
    }
    function clearPin() { currentPin = ""; updateDisplay(); }
    function updateDisplay() {
        document.getElementById('display').innerText = "*".repeat(currentPin.length) || "----";
        document.getElementById('pin-input').value = currentPin;
    }
    function submitPin() {
        if(currentPin.length === 4) document.getElementById('pin-form').submit();
    }
</script>