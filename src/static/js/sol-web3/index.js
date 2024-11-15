import { Buffer } from 'buffer';
import { SystemProgram, Connection, PublicKey, Transaction, ComputeBudgetProgram, LAMPORTS_PER_SOL } from '@solana/web3.js';
import { createAssociatedTokenAccountInstruction, createTransferInstruction, getOrCreateAssociatedTokenAccount, getAssociatedTokenAddress, TOKEN_PROGRAM_ID, ASSOCIATED_TOKEN_PROGRAM_ID } from '@solana/spl-token';

// Ensure the Buffer object is globally available
window.Buffer = window.Buffer || Buffer;

const maybePhantom = async () => {
    // console.log("maybePhantom called");
    const onlinePayBox = document.querySelector('.mcc_online_pay_box');
    if (!onlinePayBox) {
        console.error("onlinePayBox not found");
        return;
    }

    if (typeof window.solana === 'undefined') {
        console.error("Phantom wallet is not available in the window object.");
        return;
    }

    // console.log("Setting up the Phantom wallet button");
    const provider = window.solana;

    const rawCheckoutData = document.getElementById('mycryptocheckout_checkout_data');
    const checkoutData = extractData(rawCheckoutData);
    if (!checkoutData) {
        console.error("checkoutData is null");
        return;
    }
    if (!checkoutData.supports || !checkoutData.supports.phantom_wallet) {
        console.error("Phantom wallet not supported or data missing");
        return;
    }

    const phantomButton = document.createElement('div');
    phantomButton.className = "phantomwallet_link";
    phantomButton.role = "img";
    phantomButton.setAttribute("aria-label", "phantom wallet");
    const paymentButtons = document.querySelector('.payment_buttons');
    paymentButtons.appendChild(phantomButton);

    phantomButton.addEventListener('click', async () => {
        console.log("Phantom wallet button clicked");
        try {
            await provider.connect();
            // console.log("Connected to Phantom Wallet with public key:", provider.publicKey.toString());
            const network = checkoutData.supports.connection;
            const connection = new Connection(network, 'confirmed');
            const publicKey = provider.publicKey;

            if (checkoutData.currency_id === 'SOL') {
                console.log("Initiating SOL transaction");
                const transaction = new Transaction().add(
                    SystemProgram.transfer({
                        fromPubkey: publicKey,
                        toPubkey: new PublicKey(checkoutData.to),
                        lamports: LAMPORTS_PER_SOL * parseFloat(checkoutData.amount),
                    })
                );

                const latestBlockhash = await connection.getLatestBlockhash();
                transaction.recentBlockhash = latestBlockhash.blockhash;
                transaction.lastValidBlockHeight = latestBlockhash.lastValidBlockHeight;
                transaction.feePayer = publicKey;

                const signature = await provider.signAndSendTransaction(transaction);
                console.log("SOL Transaction completed with signature:", signature);
            } else {
                console.log("Initiating token transaction");
                const mintPublicKey = new PublicKey(checkoutData.currency.contract);
                const recipientPublicKey = new PublicKey(checkoutData.to);
                
                // console.log("Mint Public Key:", mintPublicKey.toString());
                // console.log("Recipient Public Key:", recipientPublicKey.toString());
                
                const transaction = new Transaction();
                
                // Adding compute unit changes and priority fees
                const modifyComputeUnits = ComputeBudgetProgram.setComputeUnitLimit({
                    units: 200000,
                });
                
                const addPriorityFee = ComputeBudgetProgram.setComputeUnitPrice({
                    microLamports: 20000,
                });
                
                transaction.add(modifyComputeUnits);
                transaction.add(addPriorityFee);
                
                let recipientTokenAccountAddress;

                const tokenAccounts = await connection.getParsedTokenAccountsByOwner(recipientPublicKey, { mint: mintPublicKey });

                if (tokenAccounts.value.length > 0) {
                    // console.log("Token account(s) found:");
                    recipientTokenAccountAddress = tokenAccounts.value[0].pubkey;  // Assume using the first account found
                    // console.log(`Account Address: ${recipientTokenAccountAddress.toString()}`);
                } else {
                    // Get the receiver's associated token account address
                    recipientTokenAccountAddress = await getAssociatedTokenAddress(
                        mintPublicKey,
                        recipientPublicKey
                    )
                    // console.log(`new Account Address: ${recipientTokenAccountAddress.toString()}`);
                    // console.log("Token account does not exist, creating new one.");

                    transaction.add(
                        createAssociatedTokenAccountInstruction(
                            publicKey, // Payer of the account creation
                            recipientTokenAccountAddress, // Account to be created
                            recipientPublicKey, // Owner of the new account
                            mintPublicKey, // Token mint
                            TOKEN_PROGRAM_ID,
                            ASSOCIATED_TOKEN_PROGRAM_ID
                        )
                    );
                }

                let senderTokenAccountAddress;
                const senderTokenAccounts = await connection.getParsedTokenAccountsByOwner(publicKey, { mint: mintPublicKey });

                if (senderTokenAccounts.value.length > 0) {
                    senderTokenAccountAddress = senderTokenAccounts.value[0].pubkey;  // Assume using the first account found
                    // console.log(`Account Address: ${senderTokenAccountAddress.toString()}`);
                } else {
                    // console.log("Token contract does not exist.");
                    return;
                }

                const amountInSmallestDenomination = Math.round(parseFloat(checkoutData.amount) * Math.pow(10, checkoutData.supports.sol20_decimal_precision));
                transaction.add(
                    createTransferInstruction(
                        senderTokenAccountAddress,
                        recipientTokenAccountAddress, // Use the previously defined variable
                        publicKey,
                        amountInSmallestDenomination,
                        [],
                        TOKEN_PROGRAM_ID
                    )
                );
                
                try {
                    const latestBlockhash = await connection.getLatestBlockhash();
                    transaction.recentBlockhash = latestBlockhash.blockhash;
                    transaction.lastValidBlockHeight = latestBlockhash.lastValidBlockHeight;
                    transaction.feePayer = publicKey;
                
                    const signature = await provider.signAndSendTransaction(transaction);
                    console.log("Token transaction completed with signature:", signature);
                } catch (error) {
                    console.error("Error during transaction:", error);
                }
            }
        } catch (error) {
            console.error("Error during transaction:", error);
        }
    });
};

window.maybePhantom = maybePhantom;

const extractData = (element) => {
    // console.log("Extracting data from element", element);
    const encodedData = element.getAttribute('data-mycryptocheckout_checkout_data');
    if (!encodedData) {
        console.error("No checkout data found!");
        return null;
    }

    try {
        const decodedData = atob(encodedData);
        const data = JSON.parse(decodedData);
        // console.log("Data extracted and parsed");
        return data;
    } catch (error) {
        console.error("Failed to decode and parse checkout data:", error);
        return null;
    }
};

// Function to execute maybePhantom once the target element is available
function setupMaybePhantom() {
    const targetElement = document.querySelector('.mcc_online_pay_box');
    if (targetElement) {
        window.maybePhantom();  // Call the function if the element is found
    } else {
        // Setup a MutationObserver to wait for the element to be added
        const observer = new MutationObserver((mutations, obs) => {
            const foundElement = document.querySelector('.mcc_online_pay_box');
            if (foundElement) {
                obs.disconnect();  // Stop observing once the element is found
                window.maybePhantom();  // Call the function
            }
        });

        // Start observing the body for added nodes
        observer.observe(document.body, {
            childList: true,  // observe direct children
            subtree: true,   // and lower descendants too
            attributes: false // do not need to observe attribute changes
        });
    }
}

// Since DOMContentLoaded may have already fired if script is async or deferred, check document.readyState
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setupMaybePhantom);
} else {
    setupMaybePhantom();  // If readyState is 'interactive' or 'complete', call it directly
}