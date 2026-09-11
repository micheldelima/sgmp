function atualizarRelatorio() {
            let mes = document.getElementById("selecionar-mes").value;
            window.location.href = "relatorios.php?mes=" + mes;
        }

        function imprimirRelatorio() {
            let mesSelecionado = document.getElementById("selecionar-mes").value; // Ex: "2025-06"
            let conteudo = document.getElementById("relatorio").innerHTML;

            // Formatar data
            const [ano, mes] = mesSelecionado.split("-");
            const nomesMeses = [
                "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho",
                "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro"
            ];
            const mesFormatado = `${nomesMeses[parseInt(mes, 10) - 1]}/${ano}`;

            let janela = window.open("", "", "width=800, height=600");
            janela.document.write("<html><head><title>Relatório</title></head><body>");
            janela.document.write("<h1 style='text-align: center;'>Relatório Mensal</h1>");
            janela.document.write("<h3 style='text-align: center;'>📆 Mês Selecionado: " + mesFormatado + "</h3>");
            janela.document.write("<hr>");
            janela.document.write(conteudo);
            janela.document.write("</body></html>");
            janela.document.close();
            janela.print();
        }

        