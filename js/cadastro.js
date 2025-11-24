const categoriaForm = document.getElementById("categoriaForm");
const produtoForm = document.getElementById("produtoForm");
const produtoCategoria = document.getElementById("produtoCategoria");

let categorias = [];

categoriaForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const nome = document.getElementById("categoriaNome").value;
  categorias.push(nome);
  atualizarCategorias();
  categoriaForm.reset();
});

function atualizarCategorias() {
  produtoCategoria.innerHTML = '<option value="">Selecione uma categoria</option>';
  categorias.forEach(cat => {
    const option = document.createElement("option");
    option.value = cat;
    option.textContent = cat;
    produtoCategoria.appendChild(option);
  });
}

produtoForm.addEventListener("submit", (e) => {
  e.preventDefault();
  const nome = document.getElementById("produtoNome").value;
  const preco = document.getElementById("produtoPreco").value;
  const imagem = document.getElementById("produtoImagem").value;
  const categoria = produtoCategoria.value;

  console.log("Produto cadastrado:", { nome, preco, imagem, categoria });
  produtoForm.reset();
});
