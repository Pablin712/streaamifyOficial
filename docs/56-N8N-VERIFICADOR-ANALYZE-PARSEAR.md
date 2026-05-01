# 56 - N8N Verificador: Analyze + Parsear (listo para copiar)

Este archivo contiene:

1. Prompt recomendado para el nodo Analyze (soporta multiples comprobantes en una sola imagen).
2. Codigo completo recomendado para el nodo Parsear.

## 1) Nodo Analyze (campo text)

Copia y pega este texto en el campo text del nodo Analyze:

```text
Analiza la imagen recibida.

----------------------------------------

1) Primero determina:

¿La imagen contiene comprobante(s) de pago REAL(ES)?

Responde con:
- es_comprobante: true | false

----------------------------------------

2) Si NO es comprobante:

Devuelve SOLO este JSON:

{
  "es_comprobante": false,
  "descripcion": "describe claramente lo que aparece en la imagen",
  "tipo_imagen": "captura_error | selfie | documento | otra",
  "confianza": 0-100
}

----------------------------------------

3) Si SÍ es comprobante:

IMPORTANTE:
- Puede haber 1 o VARIOS comprobantes en la misma foto.
- Debes detectar TODOS los comprobantes visibles.
- Si hay varios, extrae cada uno y calcula el total.

Extrae por cada comprobante:
- comprobante
- monto
- banco
- fecha
- titular (beneficiario)
- emisor (si existe, sino null)

Además devuelve resumen global:
- total_comprobantes
- monto_total_detectado (suma de montos detectados)
- banco_principal (el más repetido o dominante)
- fechas_detectadas (array de fechas detectadas)

----------------------------------------

4) Validaciones visuales:

- legible
- borroso
- editado
- realista

----------------------------------------

5) Detección avanzada:

- qr_detectado
- qr_valido_visualmente
- qr_contenido

----------------------------------------

6) Reglas IMPORTANTES:

- NO inventes datos
- Si no se ve algo: null
- Si dudas: baja confianza
- Solo analiza lo visible
- Si hay varios comprobantes, NO elijas uno: devuelve todos en comprobantes[] y suma en monto_total_detectado

----------------------------------------

7) Devuelve SOLO JSON válido:

SI ES COMPROBANTE:

{
  "es_comprobante": true,
  "total_comprobantes": number,
  "monto_total_detectado": number|null,
  "banco_principal": "string|null",
  "fechas_detectadas": ["YYYY-MM-DD"],

  "comprobantes": [
    {
      "comprobante": "string|null",
      "monto": number|null,
      "banco": "string|null",
      "fecha": "YYYY-MM-DD|null",
      "titular": "string|null",
      "emisor": "string|null"
    }
  ],

  "legible": true/false,
  "borroso": true/false,
  "editado": true/false,
  "realista": true/false,

  "qr_detectado": true/false,
  "qr_valido_visualmente": true/false,
  "qr_contenido": "string|null",

  "confianza": 0-100
}
```

## 2) Nodo Parsear (jsCode)

Copia y pega este codigo en el nodo Code (Parsear):

```javascript
// ==========================
// 1. OBTENER RESPUESTA OPENAI
// ==========================
let raw = $input.first().json['0'].content[0].text;

raw = raw
  .replace(/```json/g, '')
  .replace(/```/g, '')
  .trim();

// ==========================
// 2. PARSEAR JSON
// ==========================
let parsed;
try {
  parsed = JSON.parse(raw);
} catch (e) {
  parsed = {};
}

// ==========================
// 3. UTILIDADES
// ==========================
const normalize = (str) =>
  str?.toLowerCase()
    ?.normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .trim() || null;

const cleanReceipt = (v) => v ? String(v).replace(/\s/g, '') : null;

const parseNumber = (v) => {
  if (v === null || v === undefined) return null;
  if (typeof v === 'number') return Number.isFinite(v) ? v : null;
  const s = String(v).replace(/[^0-9,.-]/g, '').replace(',', '.');
  const n = Number(s);
  return Number.isFinite(n) ? n : null;
};

const uniq = (arr) => [...new Set(arr.filter(Boolean))];
const safeArray = (v) => Array.isArray(v) ? v : [];

// ==========================
// 4. DETECTAR TIPO
// ==========================
const esComprobante = parsed.es_comprobante === true;

// ==========================
// 5. SI ES COMPROBANTE
// ==========================
if (esComprobante) {
  // Soporta formato nuevo (comprobantes[]) y viejo (campos planos)
  let comprobantes = safeArray(parsed.comprobantes);

  if (!comprobantes.length) {
    comprobantes = [{
      comprobante: parsed.comprobante ?? null,
      monto: parsed.monto ?? null,
      banco: parsed.banco ?? parsed.banco_principal ?? null,
      fecha: parsed.fecha ?? null,
      titular: parsed.titular ?? null,
      emisor: parsed.emisor ?? null,
    }];
  }

  const comprobantesNorm = comprobantes.map((c) => ({
    comprobante: cleanReceipt(c?.comprobante),
    monto: parseNumber(c?.monto),
    banco: normalize(c?.banco),
    fecha: c?.fecha || null,
    titular: normalize(c?.titular),
    emisor: normalize(c?.emisor),
  }));

  const totalComprobantes =
    parseNumber(parsed.total_comprobantes) ?? comprobantesNorm.length;

  const montoTotalDetectado = (
    parseNumber(parsed.monto_total_detectado) ??
    comprobantesNorm.reduce((sum, c) => sum + (c.monto ?? 0), 0)
  );

  const bancoPrincipal =
    normalize(parsed.banco_principal) ||
    comprobantesNorm.find(c => c.banco)?.banco ||
    normalize(parsed.banco) ||
    null;

  const fechasDetectadas = uniq([
    ...safeArray(parsed.fechas_detectadas),
    ...comprobantesNorm.map(c => c.fecha),
    parsed.fecha || null
  ]);

  // comprobante principal: el de mayor monto, si existe
  const conMonto = comprobantesNorm.filter(c => c.monto !== null);
  const principal = conMonto.length
    ? conMonto.sort((a, b) => (b.monto ?? 0) - (a.monto ?? 0))[0]
    : (comprobantesNorm[0] || {});

  return [
    {
      json: {
        tipo: "comprobante",
        es_comprobante: true,

        data: {
          comprobante: principal.comprobante ?? cleanReceipt(parsed.comprobante) ?? null,
          monto: montoTotalDetectado ?? null,
          monto_total_detectado: montoTotalDetectado ?? null,
          total_comprobantes: totalComprobantes ?? 1,

          banco: bancoPrincipal,
          bancos_detectados: uniq(comprobantesNorm.map(c => c.banco)),

          fecha: principal.fecha ?? parsed.fecha ?? null,
          fechas_detectadas: fechasDetectadas,

          titular: principal.titular ?? normalize(parsed.titular) ?? null,
          emisor: principal.emisor ?? normalize(parsed.emisor) ?? null,

          comprobantes: comprobantesNorm
        },

        validacion: {
          legible: parsed.legible ?? null,
          borroso: parsed.borroso ?? null,
          editado: parsed.editado ?? null,
          realista: parsed.realista ?? null,
          confianza: parseNumber(parsed.confianza),
        },

        qr: {
          detectado: parsed.qr_detectado ?? false,
          valido: parsed.qr_valido_visualmente ?? false,
          contenido: parsed.qr_contenido ?? null,
        },

        raw
      }
    }
  ];
}

// ==========================
// 6. SI NO ES COMPROBANTE
// ==========================
const descripcion = parsed.descripcion || "No se pudo describir";
return [
  {
    json: {
      tipo: "imagen",
      es_comprobante: false,
      content: "<imagen>" + descripcion + "<imagen>",
      tipo_imagen: parsed.tipo_imagen || "desconocido",
      confianza: parseNumber(parsed.confianza),
      raw
    }
  }
];
```

## 3) Resultado esperado para imagen con 2 comprobantes

Para una imagen como la de dos depositos (por ejemplo 1.00 y 3.50), el flujo debe terminar con:

- data.total_comprobantes = 2
- data.monto_total_detectado = 4.5
- data.monto = 4.5

Si el modelo devuelve solo un comprobante, revisa principalmente el prompt del nodo Analyze y el input real que esta llegando en base64.
