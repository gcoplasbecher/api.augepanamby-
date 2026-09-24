<?php

use App\Support\PhoneNumber;

test('extrai somente dígitos de strings com máscaras', function () {
    expect(PhoneNumber::clean('(11) 91917-0763'))->toBe('11919170763')
        ->and(PhoneNumber::clean('+55 (11) 91917-0763'))->toBe('5511919170763')
        ->and(PhoneNumber::clean('11 3234-5678'))->toBe('1132345678')
        ->and(PhoneNumber::clean(null))->toBe('');
});

test('valida telefones celulares e fixos brasileiros válidos', function () {
    // Celulares válidos (11 dígitos, 9 após DDD)
    expect(PhoneNumber::isValidBr('(11) 91917-0763'))->toBeTrue()
        ->and(PhoneNumber::isValidBr('11919170763'))->toBeTrue()
        ->and(PhoneNumber::isValidBr('+5511919170763'))->toBeTrue()
        ->and(PhoneNumber::isValidBr('(21) 98888-7777'))->toBeTrue();

    // Fixos válidos (10 dígitos)
    expect(PhoneNumber::isValidBr('(11) 3234-5678'))->toBeTrue()
        ->and(PhoneNumber::isValidBr('1132345678'))->toBeTrue()
        ->and(PhoneNumber::isValidBr('+551132345678'))->toBeTrue();
});

test('rejeita números de telefone inválidos', function () {
    expect(PhoneNumber::isValidBr('123456'))->toBeFalse()
        ->and(PhoneNumber::isValidBr(''))->toBeFalse()
        ->and(PhoneNumber::isValidBr(null))->toBeFalse()
        ->and(PhoneNumber::isValidBr('(01) 91917-0763'))->toBeFalse() // DDD 01 não existe
        ->and(PhoneNumber::isValidBr('(11) 81917-0763'))->toBeFalse() // Celular com 11 dígitos iniciando sem 9
        ->and(PhoneNumber::isValidBr('11999999999999999'))->toBeFalse(); // Comprimento absurdo
});

test('converte para padrão E.164 internacional', function () {
    expect(PhoneNumber::toE164('(11) 91917-0763'))->toBe('+5511919170763')
        ->and(PhoneNumber::toE164('1132345678'))->toBe('+551132345678')
        ->and(PhoneNumber::toE164('+5511919170763'))->toBe('+5511919170763')
        ->and(PhoneNumber::toE164(null))->toBeNull();
});

test('formata para exibição padrão brasileira', function () {
    expect(PhoneNumber::formatBr('11919170763'))->toBe('(11) 91917-0763')
        ->and(PhoneNumber::formatBr('+5511919170763'))->toBe('(11) 91917-0763')
        ->and(PhoneNumber::formatBr('1132345678'))->toBe('(11) 3234-5678');
});
