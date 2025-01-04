// public/js/stripe.js
const stripe = Stripe(stripeKey);
const elements = stripe.elements();
const cardElement = elements.create('card');
cardElement.mount('#card-element');

const cardHolderName = document.getElementById('card-holder-name');
const cardButton = document.getElementById('card-button');
const cardForm = document.getElementById('card-form');
const errorList = document.getElementById('error-list');

cardButton.addEventListener('click', async (e) => {
    e.preventDefault();
    cardButton.disabled = true;
    
    try {
        const { setupIntent, error } = await stripe.confirmCardSetup(
            cardButton.dataset.secret, {
                payment_method: {
                    card: cardElement,
                    billing_details: { name: cardHolderName.value }
                }
            }
        );

        if (error) {
            // 显示错误信息
            errorList.innerHTML = `<li>${error.message}</li>`;
            cardButton.disabled = false;
            return;
        }

        // 创建隐藏的 input 来传递 payment method ID
        const hiddenInput = document.createElement('input');
        hiddenInput.setAttribute('type', 'hidden');
        hiddenInput.setAttribute('name', 'paymentMethodId');
        hiddenInput.setAttribute('value', setupIntent.payment_method);
        cardForm.appendChild(hiddenInput);

        // 提交表单
        cardForm.submit();
        
    } catch (err) {
        errorList.innerHTML = `<li>支付处理中出现错误</li>`;
        cardButton.disabled = false;
    }
});