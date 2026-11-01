const PptxGenJS = require('pptxgenjs');
const path = require('path');
const fs = require('fs');

const OUT_DIR = path.join(__dirname);
const SHOTS = path.join(__dirname, 'screenshots');
const DEST = path.join(__dirname, '..', '..', 'public', 'apresentacao');
const DEST_FILE = path.join(DEST, 'FARPOP-Apresentacao.pptx');

const VERDE = '0F766E';
const VERDE_ESCURO = '064E3B';
const VERDE_CLARO = 'ECFDF5';
const TEXTO = '134E4A';
const BRANCO = 'FFFFFF';

function shot(nome) {
  const p = path.join(SHOTS, nome);
  return fs.existsSync(p) ? p : null;
}

function addBg(slide, pptx) {
  slide.addShape(pptx.ShapeType.rect, {
    x: 0, y: 0, w: '100%', h: '100%',
    fill: { color: VERDE_CLARO },
  });
}

function addHeaderBar(slide, pptx) {
  slide.addShape(pptx.ShapeType.rect, {
    x: 0, y: 0, w: '100%', h: 0.55,
    fill: { color: VERDE_ESCURO },
  });
  slide.addText('FARPOP — Farmácia Popular | Prefeitura de Mesquita', {
    x: 0.3, y: 0.12, w: 9, h: 0.35,
    fontSize: 12, color: BRANCO, fontFace: 'Calibri',
  });
}

function addFooter(slide, pptx, n, total) {
  slide.addShape(pptx.ShapeType.rect, {
    x: 0, y: 5.25, w: '100%', h: 0.38,
    fill: { color: VERDE },
  });
  slide.addText(`${n} / ${total}`, {
    x: 8.5, y: 5.32, w: 1.3, h: 0.25,
    fontSize: 11, color: BRANCO, align: 'right', fontFace: 'Calibri',
  });
  slide.addText('Sistema de Gestão Farmacêutica Municipal', {
    x: 0.3, y: 5.32, w: 7, h: 0.25,
    fontSize: 11, color: BRANCO, fontFace: 'Calibri',
  });
}

function addScreenSlide(pptx, opts) {
  const { titulo, subtitulo, imagem, numero, total, bullets } = opts;
  const slide = pptx.addSlide();
  addBg(slide, pptx);
  addHeaderBar(slide, pptx);

  slide.addText(titulo, {
    x: 0.35, y: 0.7, w: 9.3, h: 0.4,
    fontSize: 22, bold: true, color: VERDE_ESCURO, fontFace: 'Calibri',
  });
  if (subtitulo) {
    slide.addText(subtitulo, {
      x: 0.35, y: 1.05, w: 9.3, h: 0.3,
      fontSize: 13, color: TEXTO, fontFace: 'Calibri',
    });
  }

  const img = shot(imagem);
  if (img) {
    slide.addImage({
      path: img,
      x: 0.35, y: 1.4, w: bullets ? 6.4 : 9.3, h: bullets ? 3.6 : 3.7,
      sizing: { type: 'contain', w: bullets ? 6.4 : 9.3, h: bullets ? 3.6 : 3.7 },
    });
  } else {
    slide.addShape(pptx.ShapeType.roundRect, {
      x: 0.35, y: 1.4, w: bullets ? 6.4 : 9.3, h: 3.6,
      fill: { color: 'D1FAE5' },
    });
    slide.addText('Tela do módulo', {
      x: 0.35, y: 2.9, w: bullets ? 6.4 : 9.3, h: 0.4,
      align: 'center', color: VERDE, fontSize: 16,
    });
  }

  if (bullets && bullets.length) {
    slide.addShape(pptx.ShapeType.roundRect, {
      x: 6.95, y: 1.4, w: 2.7, h: 3.6,
      fill: { color: BRANCO },
      shadow: { type: 'outer', color: '000000', blur: 6, opacity: 0.1, offset: 2 },
    });
    slide.addText('Destaques', {
      x: 7.1, y: 1.55, w: 2.4, h: 0.3,
      fontSize: 13, bold: true, color: VERDE, fontFace: 'Calibri',
    });
    slide.addText(bullets.map((b) => ({ text: b, options: { bullet: true } })), {
      x: 7.1, y: 1.95, w: 2.4, h: 2.8,
      fontSize: 12, color: TEXTO, fontFace: 'Calibri', valign: 'top',
    });
  }

  addFooter(slide, pptx, numero, total);
}

(async () => {
  const pptx = new PptxGenJS();
  pptx.defineLayout({ name: 'WIDE', width: 10, height: 5.625 });
  pptx.layout = 'WIDE';
  pptx.author = 'FARPOP';
  pptx.title = 'FARPOP — Apresentação do Sistema';
  pptx.subject = 'Farmácia Popular — Prefeitura de Mesquita';

  const total = 15;

  // 1. Capa
  {
    const slide = pptx.addSlide();
    slide.addShape(pptx.ShapeType.rect, {
      x: 0, y: 0, w: '100%', h: '100%',
      fill: { color: VERDE_ESCURO },
    });
    slide.addShape(pptx.ShapeType.rect, {
      x: 0, y: 4.2, w: '100%', h: 1.425,
      fill: { color: VERDE },
    });
    slide.addText('FARPOP', {
      x: 0.5, y: 1.3, w: 9, h: 0.7,
      fontSize: 48, bold: true, color: BRANCO, fontFace: 'Calibri',
    });
    slide.addText('Sistema de Gestão Farmacêutica Municipal', {
      x: 0.5, y: 2.05, w: 9, h: 0.4,
      fontSize: 20, color: 'A7F3D0', fontFace: 'Calibri',
    });
    slide.addText('Farmácia Popular — Prefeitura de Mesquita / RJ', {
      x: 0.5, y: 2.55, w: 9, h: 0.35,
      fontSize: 16, color: BRANCO, fontFace: 'Calibri',
    });
    slide.addText('Apresentação das principais funcionalidades  •  Versão 1.1 / 1.2', {
      x: 0.5, y: 4.55, w: 9, h: 0.35,
      fontSize: 14, color: BRANCO, fontFace: 'Calibri',
    });
    addFooter(slide, pptx, 1, total);
  }

  // 2. O que é
  {
    const slide = pptx.addSlide();
    addBg(slide, pptx);
    addHeaderBar(slide, pptx);
    slide.addText('O que é o FARPOP?', {
      x: 0.4, y: 0.8, w: 9, h: 0.45,
      fontSize: 26, bold: true, color: VERDE_ESCURO, fontFace: 'Calibri',
    });
    const cards = [
      { t: 'Estoque', d: 'Controle por lote (PEPS), validade e unidade de saúde' },
      { t: 'Dispensa', d: 'Saída de medicamentos com comprovante e vínculo ao paciente' },
      { t: 'Transferências', d: 'Envio, recebimento e solicitações entre unidades' },
      { t: 'Alertas', d: 'Estoque baixo, validade, ruptura e pedidos de reposição' },
      { t: 'Relatórios', d: 'Exportação CSV, impressão e indicadores de gestão' },
      { t: 'Integrações', d: 'PEC/e-SUS, Hórus e chat interno entre equipes' },
    ];
    cards.forEach((c, i) => {
      const col = i % 3;
      const row = Math.floor(i / 3);
      const x = 0.4 + col * 3.15;
      const y = 1.5 + row * 1.55;
      slide.addShape(pptx.ShapeType.roundRect, {
        x, y, w: 3, h: 1.35,
        fill: { color: BRANCO },
        shadow: { type: 'outer', color: '000000', blur: 5, opacity: 0.08, offset: 1 },
      });
      slide.addText(c.t, {
        x: x + 0.15, y: y + 0.2, w: 2.7, h: 0.35,
        fontSize: 16, bold: true, color: VERDE, fontFace: 'Calibri',
      });
      slide.addText(c.d, {
        x: x + 0.15, y: y + 0.55, w: 2.7, h: 0.6,
        fontSize: 12, color: TEXTO, fontFace: 'Calibri',
      });
    });
    addFooter(slide, pptx, 2, total);
  }

  // 3. Login
  addScreenSlide(pptx, {
    titulo: 'Acesso ao sistema',
    subtitulo: 'Tela de login com identidade visual da Prefeitura de Mesquita',
    imagem: '01-login.png',
    numero: 3,
    total,
    bullets: ['Usuário e senha', 'Perfis hierárquicos', 'Bloqueio por tentativas'],
  });

  // 4. Dashboard
  addScreenSlide(pptx, {
    titulo: 'Dashboard (Início)',
    subtitulo: 'Visão geral com KPIs de estoque, alertas, transferências e validade',
    imagem: '02-dashboard.png',
    numero: 4,
    total,
    bullets: ['KPIs em tempo real', 'Visão global ou por unidade', 'Atalhos operacionais'],
  });

  // 5. Aqui Tem
  addScreenSlide(pptx, {
    titulo: 'Aqui Tem Remédio',
    subtitulo: 'Consulta rápida de disponibilidade de medicamentos por unidade',
    imagem: '03-aqui-tem.png',
    numero: 5,
    total,
    bullets: ['Busca por medicamento', 'Saldo por unidade', 'Perfil consulta'],
  });

  // 6. Estoque
  addScreenSlide(pptx, {
    titulo: 'Consulta de estoque',
    subtitulo: 'Saldos com semáforo (verde / amarelo / vermelho) por unidade',
    imagem: '04-estoque.png',
    numero: 6,
    total,
    bullets: ['Semáforo de status', 'Filtros por unidade', 'Visão consolidada'],
  });

  // 7. Dispensa
  addScreenSlide(pptx, {
    titulo: 'Dispensa de medicamentos',
    subtitulo: 'Saídas com múltiplos itens, paciente e comprovante',
    imagem: '05-dispensa.png',
    numero: 7,
    total,
    bullets: ['Múltiplos itens', 'Baixa PEPS', 'Comprovante impresso'],
  });

  // 8. Entradas
  addScreenSlide(pptx, {
    titulo: 'Entradas de estoque',
    subtitulo: 'Compra, doação ou devolução com lote, validade e fabricante',
    imagem: '06-entradas.png',
    numero: 8,
    total,
    bullets: ['Lote e validade', 'Fornecedor vinculado', 'Tipos de entrada'],
  });

  // 9. Transferências
  addScreenSlide(pptx, {
    titulo: 'Transferências entre unidades',
    subtitulo: 'Envio direto, entre unidades, em trânsito e recebimento',
    imagem: '07-transferencias.png',
    numero: 9,
    total,
    bullets: ['Guia de transferência', 'Código de barras', 'Recebimento na unidade'],
  });

  // 10. Solicitações
  addScreenSlide(pptx, {
    titulo: 'Solicitações de transferência',
    subtitulo: 'Fluxo: solicitar → aprovar/recusar → separar → enviar',
    imagem: '08-solicitacoes.png',
    numero: 10,
    total,
    bullets: ['Aprovação na central', 'Separação de itens', 'Gera transferência'],
  });

  // 11. Alertas
  addScreenSlide(pptx, {
    titulo: 'Central de alertas',
    subtitulo: 'Estoque baixo, validade, pendências, ruptura e pedidos',
    imagem: '09-alertas.png',
    numero: 11,
    total,
    bullets: ['Estoque crítico', 'Validade próxima', 'Pedido de reposição'],
  });

  // 12. Relatórios
  addScreenSlide(pptx, {
    titulo: 'Relatórios e indicadores',
    subtitulo: 'Hub de relatórios com exportação CSV e impressão',
    imagem: '10-relatorios.png',
    numero: 12,
    total,
    bullets: ['Estoque e validade', 'Dispensas e consumo', 'Curva ABC'],
  });

  // 13. Cadastros
  addScreenSlide(pptx, {
    titulo: 'Cadastros',
    subtitulo: 'Medicamentos, unidades de saúde e usuários',
    imagem: '11-medicamentos.png',
    numero: 13,
    total,
    bullets: ['REMUME (CSV)', 'Unidades (CNES)', 'Perfis de acesso'],
  });

  // 14. Integrações
  addScreenSlide(pptx, {
    titulo: 'Integrações e governança',
    subtitulo: 'PEC/e-SUS, Hórus, auditoria e configurações',
    imagem: '14-integracoes.png',
    numero: 14,
    total,
    bullets: ['Busca cidadão (CPF)', 'Exportação Hórus', 'Auditoria e backup'],
  });

  // 15. Encerramento
  {
    const slide = pptx.addSlide();
    slide.addShape(pptx.ShapeType.rect, {
      x: 0, y: 0, w: '100%', h: '100%',
      fill: { color: VERDE_ESCURO },
    });
    slide.addText('Obrigado', {
      x: 0.5, y: 1.5, w: 9, h: 0.6,
      fontSize: 40, bold: true, color: BRANCO, fontFace: 'Calibri',
    });
    slide.addText('FARPOP — Farmácia Popular\nPrefeitura de Mesquita / RJ', {
      x: 0.5, y: 2.3, w: 9, h: 0.8,
      fontSize: 18, color: 'A7F3D0', fontFace: 'Calibri',
    });
    slide.addText('Gestão farmacêutica municipal com estoque, dispensa, transferências,\nalertas, relatórios e integrações em um só sistema.', {
      x: 0.5, y: 3.4, w: 9, h: 0.7,
      fontSize: 14, color: BRANCO, fontFace: 'Calibri',
    });
    addFooter(slide, pptx, 15, total);
  }

  fs.mkdirSync(DEST, { recursive: true });
  await pptx.writeFile({ fileName: DEST_FILE });
  // também em storage/apresentacao
  const localCopy = path.join(OUT_DIR, 'FARPOP-Apresentacao.pptx');
  fs.copyFileSync(DEST_FILE, localCopy);
  console.log('PPTX gerado:', DEST_FILE);
  console.log('Cópia local:', localCopy);
})();
