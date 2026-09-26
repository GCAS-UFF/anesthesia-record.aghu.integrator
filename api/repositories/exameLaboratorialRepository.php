<?php

class exameLaboratorialRepository extends baseRepository
{
    /**
     * Mapeamento dos códigos de analito do AGHU (resultados_exames_laboratoriais.codigo_exame)
     * para um contrato semântico estável consumido pela API SIGA.
     */
    public const ANALITOS = [
        'HEM_HEMACIAS'   => ['grupo' => 'HEMOGRAMA',       'analito' => 'HEMACIAS'],
        'HEM_HB'         => ['grupo' => 'HEMOGRAMA',       'analito' => 'HEMOGLOBINA'],
        'HEM_HT'         => ['grupo' => 'HEMOGRAMA',       'analito' => 'HEMATOCRITO'],
        'HEM_VCM'        => ['grupo' => 'HEMOGRAMA',       'analito' => 'VCM'],
        'HEM_HCM'        => ['grupo' => 'HEMOGRAMA',       'analito' => 'HCM'],
        'HEM_CHCM'       => ['grupo' => 'HEMOGRAMA',       'analito' => 'CHCM'],
        'HEM_RDW'        => ['grupo' => 'HEMOGRAMA',       'analito' => 'RDW'],
        'HEM_LEUCOCITOS' => ['grupo' => 'HEMOGRAMA',       'analito' => 'LEUCOCITOS'],
        'HEM_PLAQUETAS'  => ['grupo' => 'HEMOGRAMA',       'analito' => 'PLAQUETAS'],
        'COAG_TP'        => ['grupo' => 'COAGULOGRAMA',    'analito' => 'TP'],
        'COAG_INR'       => ['grupo' => 'COAGULOGRAMA',    'analito' => 'INR'],
        'COAG_TTPA'      => ['grupo' => 'COAGULOGRAMA',    'analito' => 'TTPA'],
        'HEP_TGO'        => ['grupo' => 'FUNCAO_HEPATICA', 'analito' => 'TGO'],
        'HEP_TGP'        => ['grupo' => 'FUNCAO_HEPATICA', 'analito' => 'TGP'],
        'HEP_GGT'        => ['grupo' => 'FUNCAO_HEPATICA', 'analito' => 'GGT'],
        'HEP_FA'         => ['grupo' => 'FUNCAO_HEPATICA', 'analito' => 'FOSFATASE_ALCALINA'],
        'REN_UREIA'      => ['grupo' => 'FUNCAO_RENAL',    'analito' => 'UREIA'],
        'REN_CREATININA' => ['grupo' => 'FUNCAO_RENAL',    'analito' => 'CREATININA'],
    ];
  
    public function buscarUltimoLiberado(string $idPaciente): ?array
    {
        $linhas = $this->fetchAll("
            WITH paciente AS (
                SELECT p.id
                FROM aghu_stg.pacientes p
                WHERE p.id = :paciente
            ),
            ultima_coleta AS (
                SELECT
                    e.id,
                    e.data_solicitacao,
                    e.data_coleta,
                    e.data_liberacao,
                    e.status
                FROM aghu_stg.exames_laboratoriais e
                WHERE e.paciente_id = :pacienteExame
                  AND e.status = 'LIBERADO'
                ORDER BY
                    e.data_coleta DESC,
                    e.data_liberacao DESC NULLS LAST,
                    e.id DESC
                LIMIT 1
            )
            SELECT
                p.id AS paciente_id,
                uc.id AS exame_id,
                uc.data_solicitacao,
                uc.data_coleta,
                uc.data_liberacao,
                uc.status,
                r.codigo_exame,
                r.nome_exame,
                r.resultado,
                r.unidade,
                r.valor_referencia

            FROM paciente p

            LEFT JOIN ultima_coleta uc
                ON TRUE

            LEFT JOIN aghu_stg.resultados_exames_laboratoriais r
                ON r.exame_id = uc.id

            ORDER BY r.codigo_exame
        ", [
            ':paciente' => $idPaciente,
            ':pacienteExame' => $idPaciente
        ]);

        if (empty($linhas)) {
            return null;
        }

        $primeira = $linhas[0];

        if ($primeira['exame_id'] === null) {
            return [
                'paciente_id' => $primeira['paciente_id'],
                'exame' => null,
                'resultados' => [],
                'ausentes' => array_keys(self::ANALITOS),
                'codigos_nao_mapeados' => []
            ];
        }

        $resultados = [];
        $naoMapeados = [];
        $encontrados = [];

        foreach ($linhas as $linha) {
            $codigo = $linha['codigo_exame'];

            if ($codigo === null) {
                continue;
            }

            $codigoNormalizado = strtoupper(trim($codigo));

            if (!isset(self::ANALITOS[$codigoNormalizado])) {
                $naoMapeados[] = $codigo;
                continue;
            }

            // A constraint uq_resultado_exame_codigo garante um resultado por código na coleta;
            // a verificação evita duplicidade caso existam códigos que diferem só por caixa/espaços.
            if (isset($encontrados[$codigoNormalizado])) {
                continue;
            }

            $encontrados[$codigoNormalizado] = true;

            $resultados[] = [
                'grupo' => self::ANALITOS[$codigoNormalizado]['grupo'],
                'analito' => self::ANALITOS[$codigoNormalizado]['analito'],
                'codigo_exame' => $codigoNormalizado,
                'nome_exame' => $linha['nome_exame'],
                'valor' => $linha['resultado'] !== null ? (float)$linha['resultado'] : null,
                'unidade' => $linha['unidade'],
                'valor_referencia' => $linha['valor_referencia']
            ];
        }

        return [
            'paciente_id' => $primeira['paciente_id'],
            'exame' => [
                'id' => (int)$primeira['exame_id'],
                'data_solicitacao' => $primeira['data_solicitacao'],
                'data_coleta' => $primeira['data_coleta'],
                'data_liberacao' => $primeira['data_liberacao'],
                'status' => $primeira['status']
            ],
            'resultados' => $resultados,
            'ausentes' => array_values(array_diff(array_keys(self::ANALITOS), array_keys($encontrados))),
            'codigos_nao_mapeados' => $naoMapeados
        ];
    }
}
