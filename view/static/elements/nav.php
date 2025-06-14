<div class="botoes-fixos">
    <a href="/">Página Inicial</a>
    <a href="/posts">Postagens</a>
    <a href="/reclamar">Postar</a>
</div>

<style>
.botoes-fixos {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  padding: 10px 20px;
  display: flex;
  justify-content: center;
  gap: 20%;
  flex-wrap: wrap;
  background-color: transparent;
  z-index: 999;
}

.botoes-fixos a {
  --btn-bg: #4a90e2;
  background-color: var(--btn-bg);
  border-radius: 50px;
  padding: 10px 20px;
  font-size: 16px;
  color: white;
  text-decoration: none;
  text-align: center;
  transition: background-color 0.3s, color 0.3s;
}

.botoes-fixos a:hover {
  background-color: color-mix(in oklch, var(--btn-bg) 100%, white 15%);
  color: #333;
}

.botoes-fixos a:active {
  background-color: color-mix(in oklch, var(--btn-bg) 100%, black 10%);
}

.botoes-fixos a:focus-visible {
  outline: 3px solid var(--btn-bg);
  outline-offset: 2px;
}
</style>